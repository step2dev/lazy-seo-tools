<?php

namespace Step2dev\LazySeoTools\Services;

use Step2dev\LazySeoTools\Data\SeoAnalysisResult;
use Step2dev\LazySeoTools\Data\SeoData;

class SeoAnalyzerService
{
    public function analyze(string $title, string $description, string|array $keywords = '', string $content = '', array $context = []): array
    {
        return $this->analyzePage(array_replace($context, [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'html' => $content,
        ]))->toArray();
    }

    public function analyzeData(SeoData $data, string $html = '', array $context = []): SeoAnalysisResult
    {
        return $this->analyzePage(array_replace($data->toArray(), $context, ['html' => $html]));
    }

    public function analyzePage(array $page): SeoAnalysisResult
    {
        $errors = [];
        $warnings = [];
        $notices = [];
        $score = 100;

        $html = (string) ($page['html'] ?? $page['content'] ?? '');
        $plainText = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
        $title = trim((string) ($page['title'] ?? ''));
        $description = trim((string) ($page['description'] ?? ''));
        $canonical = trim((string) ($page['canonical_url'] ?? $page['canonicalUrl'] ?? ''));
        $robots = array_map('strtolower', (array) ($page['robots'] ?? []));
        $image = trim((string) ($page['image'] ?? ''));
        $keywords = $this->normalizeKeywords($page['keywords'] ?? []);

        $metrics = [
            'title_length' => mb_strlen($title),
            'description_length' => mb_strlen($description),
            'word_count' => str_word_count($plainText),
            'h1_count' => $this->countPattern('/<h1\b[^>]*>/i', $html),
            'h2_count' => $this->countPattern('/<h2\b[^>]*>/i', $html),
            'images_total' => $this->countPattern('/<img\b[^>]*>/i', $html),
            'images_missing_alt' => $this->countImagesMissingAlt($html),
            'internal_links' => $this->countLinks($html, true),
            'external_links' => $this->countLinks($html, false),
            'json_ld_blocks' => $this->countPattern('/<script\b[^>]*type=["\']application\/ld\+json["\'][^>]*>/i', $html),
        ];

        $this->checkTitle($title, $score, $errors, $warnings);
        $this->checkDescription($description, $score, $errors, $warnings);
        $this->checkCanonical($canonical, $score, $warnings, $notices);
        $this->checkRobots($robots, $score, $warnings);
        $this->checkContent($metrics, $score, $warnings, $notices);
        $this->checkImages($metrics, $score, $warnings);
        $this->checkSocial($page, $image, $score, $warnings, $notices);
        $this->checkStructuredData($metrics, $score, $warnings);
        $this->checkKeywords($keywords, $plainText, $score, $notices);

        return new SeoAnalysisResult(
            score: max(0, min(100, $score)),
            errors: array_values(array_unique($errors)),
            warnings: array_values(array_unique($warnings)),
            notices: array_values(array_unique($notices)),
            metrics: $metrics,
        );
    }

    protected function checkTitle(string $title, int &$score, array &$errors, array &$warnings): void
    {
        $length = mb_strlen($title);

        if ($title === '') {
            $score -= 18;
            $errors[] = 'Missing title.';

            return;
        }

        if ($length < 30) {
            $score -= 7;
            $warnings[] = 'Title is too short.';
        }

        if ($length > 65) {
            $score -= 7;
            $warnings[] = 'Title is too long.';
        }
    }

    protected function checkDescription(string $description, int &$score, array &$errors, array &$warnings): void
    {
        $length = mb_strlen($description);

        if ($description === '') {
            $score -= 18;
            $errors[] = 'Missing meta description.';

            return;
        }

        if ($length < 70) {
            $score -= 6;
            $warnings[] = 'Meta description is too short.';
        }

        if ($length > 170) {
            $score -= 6;
            $warnings[] = 'Meta description is too long.';
        }
    }

    protected function checkCanonical(string $canonical, int &$score, array &$warnings, array &$notices): void
    {
        if ($canonical === '') {
            $score -= 7;
            $warnings[] = 'Canonical URL is missing.';

            return;
        }

        if (! str_starts_with($canonical, 'http://') && ! str_starts_with($canonical, 'https://') && ! str_starts_with($canonical, '/')) {
            $score -= 4;
            $warnings[] = 'Canonical URL looks invalid.';
        } else {
            $notices[] = 'Canonical URL is present.';
        }
    }

    protected function checkRobots(array $robots, int &$score, array &$warnings): void
    {
        if ($robots === []) {
            $score -= 4;
            $warnings[] = 'Robots directive is missing.';

            return;
        }

        if (in_array('noindex', $robots, true)) {
            $score -= 12;
            $warnings[] = 'Page is marked as noindex.';
        }
    }

    protected function checkContent(array $metrics, int &$score, array &$warnings, array &$notices): void
    {
        if ($metrics['word_count'] < 250) {
            $score -= 8;
            $warnings[] = 'Content is thin.';
        }

        if ($metrics['h1_count'] === 0) {
            $score -= 6;
            $warnings[] = 'Missing H1 heading.';
        }

        if ($metrics['h1_count'] > 1) {
            $score -= 4;
            $warnings[] = 'Multiple H1 headings found.';
        }

        if ($metrics['h2_count'] === 0) {
            $notices[] = 'No H2 headings found.';
        }
    }

    protected function checkImages(array $metrics, int &$score, array &$warnings): void
    {
        if ($metrics['images_missing_alt'] > 0) {
            $score -= min(10, $metrics['images_missing_alt'] * 2);
            $warnings[] = 'Some images are missing alt attributes.';
        }
    }

    protected function checkSocial(array $page, string $image, int &$score, array &$warnings, array &$notices): void
    {
        $hasOg = (bool) ($page['og'] ?? false) || filled($page['og_title'] ?? null) || filled($image);
        $hasTwitter = (bool) ($page['twitter'] ?? false) || filled($page['twitter_title'] ?? null) || filled($image);

        if (! $hasOg) {
            $score -= 5;
            $warnings[] = 'OpenGraph data is missing.';
        }

        if (! $hasTwitter) {
            $score -= 4;
            $warnings[] = 'Twitter card data is missing.';
        }

        if ($image !== '') {
            $notices[] = 'Social image is present.';
        }
    }

    protected function checkStructuredData(array $metrics, int &$score, array &$warnings): void
    {
        if ($metrics['json_ld_blocks'] === 0) {
            $score -= 5;
            $warnings[] = 'JSON-LD structured data is missing.';
        }
    }

    protected function checkKeywords(array $keywords, string $plainText, int &$score, array &$notices): void
    {
        if ($keywords === [] || $plainText === '') {
            return;
        }

        $lowerText = mb_strtolower($plainText);
        $used = 0;

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($lowerText, mb_strtolower($keyword))) {
                $used++;
            }
        }

        if ($used === 0) {
            $score -= 4;
            $notices[] = 'Target keywords were not found in content.';
        }
    }

    protected function normalizeKeywords(string|array $keywords): array
    {
        if (is_string($keywords)) {
            $keywords = explode(',', $keywords);
        }

        return array_values(array_filter(array_map(static fn (mixed $keyword): string => trim((string) $keyword), $keywords)));
    }

    protected function countPattern(string $pattern, string $html): int
    {
        if ($html === '') {
            return 0;
        }

        return preg_match_all($pattern, $html) ?: 0;
    }

    protected function countImagesMissingAlt(string $html): int
    {
        if ($html === '') {
            return 0;
        }

        preg_match_all('/<img\b[^>]*>/i', $html, $matches);

        return collect($matches[0] ?? [])
            ->filter(fn (string $tag): bool => ! preg_match('/\salt\s*=\s*(["\']).*?\1/i', $tag))
            ->count();
    }

    protected function countLinks(string $html, bool $internal): int
    {
        if ($html === '') {
            return 0;
        }

        preg_match_all('/<a\b[^>]*href\s*=\s*(["\'])(.*?)\1[^>]*>/i', $html, $matches);

        return collect($matches[2] ?? [])
            ->filter(function (string $href) use ($internal): bool {
                $isExternal = str_starts_with($href, 'http://') || str_starts_with($href, 'https://') || str_starts_with($href, '//');

                return $internal ? ! $isExternal : $isExternal;
            })
            ->count();
    }
}
