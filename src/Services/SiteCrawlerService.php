<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Support\Facades\Http;
use Step2dev\LazySeoTools\Data\CrawledPage;
use Step2dev\LazySeoTools\Data\CrawlResult;

class SiteCrawlerService
{
    public function __construct(
        protected SeoAnalyzerService $analyzer,
        protected UrlNormalizer $urls,
    ) {}

    public function crawl(string $startUrl, array $options = []): CrawlResult
    {
        $startUrl = $this->urls->normalize($startUrl) ?? $startUrl;
        $maxPages = (int) ($options['max_pages'] ?? config('lazy-seo.crawler.max_pages', 50));
        $timeout = (int) ($options['timeout'] ?? config('lazy-seo.crawler.timeout', 10));
        $respectNoindex = (bool) ($options['respect_noindex'] ?? config('lazy-seo.crawler.respect_noindex', false));
        $userAgent = (string) ($options['user_agent'] ?? config('lazy-seo.crawler.user_agent', 'LazySeoBot/1.0'));
        $exclude = (array) ($options['exclude'] ?? config('lazy-seo.crawler.exclude', []));

        $queue = [$startUrl];
        $visited = [];
        $pages = [];
        $incoming = [];
        $brokenLinks = [];
        $redirectChains = [];

        while ($queue !== [] && count($visited) < $maxPages) {
            $url = array_shift($queue);

            if (! is_string($url) || isset($visited[$url]) || $this->isExcluded($url, $exclude)) {
                continue;
            }

            $visited[$url] = true;
            $page = $this->fetch($url, $timeout, $userAgent);
            $pages[] = $page;

            if ($page->redirects !== []) {
                $redirectChains[$url] = $page->redirects;
            }

            if (! $page->ok() || ($respectNoindex && in_array('noindex', $page->robots, true))) {
                continue;
            }

            foreach ($page->links as $link) {
                $target = $link['url'] ?? null;

                if (! is_string($target)) {
                    continue;
                }

                $incoming[$target] ??= [];
                $incoming[$target][] = $url;

                if (! $this->urls->sameHost($target, $startUrl)) {
                    continue;
                }

                if (! isset($visited[$target]) && ! in_array($target, $queue, true) && count($visited) + count($queue) < $maxPages) {
                    $queue[] = $target;
                }
            }
        }

        $knownStatuses = collect($pages)->keyBy('url')->map(fn (CrawledPage $page): int => $page->status)->all();

        foreach ($incoming as $target => $sources) {
            if (isset($knownStatuses[$target]) && $knownStatuses[$target] >= 400) {
                $brokenLinks[$target] = array_values(array_unique($sources));
            }
        }

        return new CrawlResult(
            startUrl: $startUrl,
            pages: $pages,
            brokenLinks: $brokenLinks,
            redirectChains: $this->filterRedirectChains($redirectChains),
            duplicateTitles: $this->duplicates($pages, 'title'),
            duplicateDescriptions: $this->duplicates($pages, 'description'),
            canonicalConflicts: $this->canonicalConflicts($pages),
            orphanPages: $this->orphanPages($pages, $incoming, $startUrl),
        );
    }

    protected function fetch(string $url, int $timeout, string $userAgent): CrawledPage
    {
        try {
            $response = Http::timeout($timeout)
                ->withHeaders(['User-Agent' => $userAgent])
                ->withOptions(['allow_redirects' => ['track_redirects' => true]])
                ->get($url);

            $html = (string) $response->body();
            $redirects = (array) ($response->handlerStats()['redirect_url'] ?? []);

            if ($redirects === [] && $response->header('X-Guzzle-Redirect-History')) {
                $redirects = array_filter(array_map('trim', explode(',', (string) $response->header('X-Guzzle-Redirect-History'))));
            }

            return $this->parse($url, $response->status(), $html, $redirects);
        } catch (\Throwable $e) {
            return new CrawledPage(url: $url, status: 0, error: $e->getMessage());
        }
    }

    /** @param array<int, string> $redirects */
    public function parse(string $url, int $status, string $html, array $redirects = []): CrawledPage
    {
        $title = $this->firstMatch('/<title[^>]*>(.*?)<\/title>/is', $html);
        $description = $this->metaContent($html, 'description');
        $canonical = $this->linkHref($html, 'canonical');
        $robots = array_map('trim', explode(',', strtolower((string) $this->metaContent($html, 'robots'))));
        $robots = array_values(array_filter($robots));
        $headings = $this->headings($html);
        $links = $this->links($html, $url);
        $images = $this->images($html, $url);

        $analysis = $this->analyzer->analyzePage([
            'title' => $title,
            'description' => $description,
            'canonical_url' => $canonical,
            'robots' => $robots,
            'image' => $this->metaProperty($html, 'og:image') ?: $this->metaContent($html, 'twitter:image'),
            'og' => (bool) $this->metaProperty($html, 'og:title'),
            'twitter' => (bool) $this->metaContent($html, 'twitter:title'),
            'html' => $html,
        ]);

        return new CrawledPage(
            url: $url,
            status: $status,
            title: $title,
            description: $description,
            canonical: $canonical,
            robots: $robots,
            headings: $headings,
            links: $links,
            images: $images,
            redirects: $redirects,
            analysis: $analysis,
        );
    }

    protected function firstMatch(string $pattern, string $html): ?string
    {
        return preg_match($pattern, $html, $matches) ? trim(strip_tags(html_entity_decode($matches[1]))) : null;
    }

    protected function metaContent(string $html, string $name): ?string
    {
        $name = preg_quote($name, '/');

        if (preg_match('/<meta[^>]+name=["\']'.$name.'["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/i', $html, $matches)) {
            return trim(html_entity_decode($matches[1]));
        }

        if (preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']'.$name.'["\'][^>]*>/i', $html, $matches)) {
            return trim(html_entity_decode($matches[1]));
        }

        return null;
    }

    protected function metaProperty(string $html, string $property): ?string
    {
        $property = preg_quote($property, '/');

        if (preg_match('/<meta[^>]+property=["\']'.$property.'["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/i', $html, $matches)) {
            return trim(html_entity_decode($matches[1]));
        }

        return null;
    }

    protected function linkHref(string $html, string $rel): ?string
    {
        $rel = preg_quote($rel, '/');

        if (preg_match('/<link[^>]+rel=["\']'.$rel.'["\'][^>]+href=["\']([^"\']*)["\'][^>]*>/i', $html, $matches)) {
            return trim(html_entity_decode($matches[1]));
        }

        return null;
    }

    protected function headings(string $html): array
    {
        preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $html, $matches, PREG_SET_ORDER);

        return array_map(static fn (array $match): array => [
            'level' => (int) $match[1],
            'text' => trim(strip_tags(html_entity_decode($match[2]))),
        ], $matches);
    }

    protected function links(string $html, string $baseUrl): array
    {
        preg_match_all('/<a\b[^>]*href\s*=\s*(["\'])(.*?)\1[^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER);

        return array_values(array_filter(array_map(function (array $match) use ($baseUrl): ?array {
            $url = $this->urls->normalize($match[2], $baseUrl);

            if (! $url) {
                return null;
            }

            return [
                'url' => $url,
                'text' => trim(strip_tags(html_entity_decode($match[3]))),
                'external' => ! $this->urls->sameHost($url, $baseUrl),
            ];
        }, $matches)));
    }

    protected function images(string $html, string $baseUrl): array
    {
        preg_match_all('/<img\b[^>]*>/i', $html, $matches);

        return array_map(function (string $tag) use ($baseUrl): array {
            preg_match('/\ssrc\s*=\s*(["\'])(.*?)\1/i', $tag, $src);
            preg_match('/\salt\s*=\s*(["\'])(.*?)\1/i', $tag, $alt);

            return [
                'src' => isset($src[2]) ? $this->urls->normalize($src[2], $baseUrl) : null,
                'alt' => $alt[2] ?? null,
                'missing_alt' => ! isset($alt[2]) || trim($alt[2]) === '',
            ];
        }, $matches[0] ?? []);
    }

    protected function isExcluded(string $url, array $exclude): bool
    {
        $path = trim(parse_url($url, PHP_URL_PATH) ?: '/', '/');

        foreach ($exclude as $pattern) {
            $pattern = trim((string) $pattern, '/');
            $regex = '#^'.str_replace('\\*', '.*', preg_quote($pattern, '#')).'$#u';

            if (preg_match($regex, $path)) {
                return true;
            }
        }

        return false;
    }

    protected function filterRedirectChains(array $chains): array
    {
        return array_filter($chains, static fn (array $chain): bool => count($chain) > 1);
    }

    /** @param array<int, CrawledPage> $pages */
    protected function duplicates(array $pages, string $field): array
    {
        $values = [];

        foreach ($pages as $page) {
            $value = trim((string) $page->{$field});

            if ($value === '') {
                continue;
            }

            $values[$value][] = $page->url;
        }

        return array_filter($values, static fn (array $urls): bool => count($urls) > 1);
    }

    /** @param array<int, CrawledPage> $pages */
    protected function canonicalConflicts(array $pages): array
    {
        $conflicts = [];

        foreach ($pages as $page) {
            if (! $page->canonical) {
                continue;
            }

            $canonical = $this->urls->normalize($page->canonical, $page->url);

            if ($canonical && $canonical !== $page->url) {
                $conflicts[$page->url] = $canonical;
            }
        }

        return $conflicts;
    }

    /** @param array<int, CrawledPage> $pages */
    protected function orphanPages(array $pages, array $incoming, string $startUrl): array
    {
        return collect($pages)
            ->map(fn (CrawledPage $page): string => $page->url)
            ->filter(fn (string $url): bool => $url !== $startUrl && empty($incoming[$url]))
            ->values()
            ->all();
    }
}
