<?php

namespace Step2dev\LazySeoTools\Services;

use Step2dev\LazySeoTools\Data\ContentIntelligenceResult;

class ContentIntelligenceService
{
    public function analyze(string $html, array|string $targetKeywords = [], ?string $baseUrl = null): ContentIntelligenceResult
    {
        $score = 100;
        $warnings = [];
        $suggestions = [];
        $text = $this->plainText($html);
        $words = $this->words($text);
        $sentences = $this->sentences($text);
        $headings = $this->headings($html);
        $images = $this->images($html);
        $links = $this->links($html, $baseUrl);
        $keywordMetrics = $this->keywordMetrics($words, $targetKeywords);

        $wordCount = count($words);
        $avgSentenceWords = count($sentences) > 0 ? round($wordCount / count($sentences), 2) : 0.0;
        $missingAlt = count(array_filter($images, static fn (array $image): bool => ($image['missing_alt'] ?? false) === true));
        $internalLinks = count(array_filter($links, static fn (array $link): bool => ($link['type'] ?? null) === 'internal'));

        if ($wordCount < (int) config('lazy-seo.content_intelligence.min_words', 300)) {
            $score -= 12;
            $warnings[] = 'Content is probably too thin for organic search.';
            $suggestions[] = 'Add more useful original content, examples, FAQs or comparison sections.';
        }

        if ($headings['h1_count'] === 0) {
            $score -= 8;
            $warnings[] = 'Missing H1 heading.';
        }

        if ($headings['h1_count'] > 1) {
            $score -= 6;
            $warnings[] = 'Multiple H1 headings found.';
        }

        if ($headings['h2_count'] === 0) {
            $score -= 4;
            $suggestions[] = 'Split content into H2 sections for better structure.';
        }

        if ($avgSentenceWords > (int) config('lazy-seo.content_intelligence.max_readability_sentence_words', 22)) {
            $score -= 6;
            $warnings[] = 'Average sentence length is high.';
            $suggestions[] = 'Shorten long sentences to improve readability.';
        }

        if ($missingAlt > 0) {
            $score -= min(10, $missingAlt * 2);
            $warnings[] = 'Some images are missing alt text.';
        }

        if ($internalLinks < (int) config('lazy-seo.content_intelligence.internal_link_minimum', 1)) {
            $score -= 5;
            $suggestions[] = 'Add relevant internal links to strengthen topical structure.';
        }

        foreach ($keywordMetrics as $keyword => $metric) {
            if ($metric['count'] === 0) {
                $score -= 5;
                $warnings[] = "Target keyword '{$keyword}' is missing from the content.";

                continue;
            }

            if ($metric['density'] < (float) config('lazy-seo.content_intelligence.min_keyword_density', 0.3)) {
                $suggestions[] = "Keyword '{$keyword}' appears rarely; consider adding it naturally.";
            }

            if ($metric['density'] > (float) config('lazy-seo.content_intelligence.max_keyword_density', 3.5)) {
                $score -= 5;
                $warnings[] = "Keyword '{$keyword}' may be overused.";
            }
        }

        return new ContentIntelligenceResult(
            score: max(0, min(100, $score)),
            warnings: array_values(array_unique($warnings)),
            suggestions: array_values(array_unique($suggestions)),
            metrics: [
                'word_count' => $wordCount,
                'sentence_count' => count($sentences),
                'average_sentence_words' => $avgSentenceWords,
                'paragraph_count' => preg_match_all('/<p\b[^>]*>/i', $html) ?: 0,
                'missing_alt_images' => $missingAlt,
                'internal_links' => $internalLinks,
                'external_links' => count($links) - $internalLinks,
            ],
            keywords: $keywordMetrics,
            headings: $headings,
            images: $images,
            links: $links,
        );
    }

    protected function plainText(string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html))) ?? '');
    }

    /** @return array<int, string> */
    protected function words(string $text): array
    {
        preg_match_all('/[\p{L}\p{N}\']+/u', mb_strtolower($text), $matches);

        return array_values(array_filter($matches[0]));
    }

    /** @return array<int, string> */
    protected function sentences(string $text): array
    {
        return array_values(array_filter(preg_split('/[.!?]+\s*/u', $text) ?: []));
    }

    protected function headings(string $html): array
    {
        $items = [];
        preg_match_all('/<h([1-6])\b[^>]*>(.*?)<\/h\1>/is', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $items[] = [
                'level' => (int) $match[1],
                'text' => trim(strip_tags(html_entity_decode($match[2]))),
            ];
        }

        return [
            'items' => $items,
            'h1_count' => count(array_filter($items, static fn (array $heading): bool => $heading['level'] === 1)),
            'h2_count' => count(array_filter($items, static fn (array $heading): bool => $heading['level'] === 2)),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    protected function images(string $html): array
    {
        preg_match_all('/<img\b[^>]*>/i', $html, $matches);

        return array_map(function (string $tag): array {
            $src = $this->attribute($tag, 'src');
            $alt = $this->attribute($tag, 'alt');

            return [
                'src' => $src,
                'alt' => $alt,
                'missing_alt' => blank($alt),
            ];
        }, $matches[0]);
    }

    /** @return array<int, array<string, string|null>> */
    protected function links(string $html, ?string $baseUrl): array
    {
        preg_match_all('/<a\b[^>]*href\s*=\s*(["\'])(.*?)\1[^>]*>/i', $html, $matches);

        return array_map(function (string $href) use ($baseUrl): array {
            $external = str_starts_with($href, 'http://') || str_starts_with($href, 'https://') || str_starts_with($href, '//');

            if ($baseUrl && $external) {
                $baseHost = parse_url($baseUrl, PHP_URL_HOST);
                $hrefHost = parse_url($href, PHP_URL_HOST);
                $external = $baseHost !== null && $hrefHost !== null && $baseHost !== $hrefHost;
            }

            return [
                'url' => $href,
                'type' => $external ? 'external' : 'internal',
                'anchor' => null,
            ];
        }, $matches[2]);
    }

    /** @return array<string, array{count:int,density:float}> */
    protected function keywordMetrics(array $words, array|string $targetKeywords): array
    {
        if (is_string($targetKeywords)) {
            $targetKeywords = array_filter(array_map('trim', explode(',', $targetKeywords)));
        }

        $text = ' '.implode(' ', $words).' ';
        $wordCount = max(1, count($words));
        $metrics = [];

        foreach ($targetKeywords as $keyword) {
            $keyword = mb_strtolower(trim((string) $keyword));

            if ($keyword === '') {
                continue;
            }

            $count = substr_count($text, ' '.$keyword.' ');
            $metrics[$keyword] = [
                'count' => $count,
                'density' => round(($count / $wordCount) * 100, 2),
            ];
        }

        return $metrics;
    }

    protected function attribute(string $tag, string $attribute): ?string
    {
        $attribute = preg_quote($attribute, '/');

        return preg_match('/\s'.$attribute.'\s*=\s*(["\'])(.*?)\1/i', $tag, $matches)
            ? trim(html_entity_decode($matches[2]))
            : null;
    }
}
