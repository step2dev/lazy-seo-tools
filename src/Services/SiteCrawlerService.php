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
        $maxPages = max(1, (int) ($options['max_pages'] ?? config('lazy-seo.crawler.max_pages', 50)));
        $maxDepth = max(0, (int) ($options['max_depth'] ?? config('lazy-seo.crawler.max_depth', 5)));
        $timeout = max(1, (int) ($options['timeout'] ?? config('lazy-seo.crawler.timeout', 10)));
        $respectNoindex = (bool) ($options['respect_noindex'] ?? config('lazy-seo.crawler.respect_noindex', false));
        $respectRobotsTxt = (bool) ($options['respect_robots_txt'] ?? config('lazy-seo.crawler.respect_robots_txt', true));
        $checkExternalLinks = (bool) ($options['check_external_links'] ?? config('lazy-seo.crawler.check_external_links', false));
        $maxExternalLinks = max(0, (int) ($options['max_external_links'] ?? config('lazy-seo.crawler.max_external_links', 50)));
        $userAgent = (string) ($options['user_agent'] ?? config('lazy-seo.crawler.user_agent', 'LazySeoBot/1.0'));
        $exclude = (array) ($options['exclude'] ?? config('lazy-seo.crawler.exclude', []));
        $rateLimitMs = max(0, (int) ($options['rate_limit_ms'] ?? config('lazy-seo.crawler.rate_limit_ms', 250)));
        $security = $this->securityOptions($options);
        $robotsRules = $respectRobotsTxt ? $this->robotsRules($startUrl, $timeout, $userAgent, $security) : [];

        $queue = [['url' => $startUrl, 'depth' => 0]];
        $visited = [];
        $pages = [];
        $incoming = [];
        $brokenLinks = [];
        $externalBrokenLinks = [];
        $externalCandidates = [];
        $redirectChains = [];
        $lastRequestAt = null;

        while ($queue !== [] && count($visited) < $maxPages) {
            /** @var array{url: string, depth: int} $item */
            $item = array_shift($queue);
            $url = $item['url'];
            $depth = $item['depth'];

            if (isset($visited[$url]) || $this->isExcluded($url, $exclude) || ! $this->isAllowedByRobots($url, $robotsRules)) {
                continue;
            }

            $visited[$url] = true;
            $this->throttle($lastRequestAt, $rateLimitMs);
            $page = $this->fetch($url, $timeout, $userAgent, $security);
            $lastRequestAt = microtime(true);
            $pages[] = $page;

            if ($page->redirects !== []) {
                $redirectChains[$url] = $page->redirects;
            }

            if (! $page->ok() || $depth >= $maxDepth || ($respectNoindex && in_array('noindex', $page->robots, true))) {
                continue;
            }

            foreach ($page->links as $link) {
                $target = $link['url'] ?? null;

                if (! is_string($target) || ! $this->isUrlAllowed($target, $security) || ! $this->isAllowedByRobots($target, $robotsRules)) {
                    continue;
                }

                $incoming[$target] ??= [];
                $incoming[$target][] = $url;

                if (! $this->urls->sameHost($target, $startUrl)) {
                    if ($checkExternalLinks && count($externalCandidates) < $maxExternalLinks) {
                        $externalCandidates[$target] ??= [];
                        $externalCandidates[$target][] = $url;
                    }

                    continue;
                }

                $alreadyQueued = collect($queue)->contains(fn (array $queued): bool => $queued['url'] === $target);

                if (! isset($visited[$target]) && ! $alreadyQueued && count($visited) + count($queue) < $maxPages) {
                    $queue[] = ['url' => $target, 'depth' => $depth + 1];
                }
            }
        }

        $knownStatuses = collect($pages)->keyBy('url')->map(fn (CrawledPage $page): int => $page->status)->all();

        foreach ($incoming as $target => $sources) {
            if (isset($knownStatuses[$target]) && $knownStatuses[$target] >= 400) {
                $brokenLinks[$target] = array_values(array_unique($sources));
            }
        }

        if ($checkExternalLinks) {
            $externalBrokenLinks = $this->checkExternalLinks($externalCandidates, $timeout, $userAgent, $security, $rateLimitMs);
        }

        return new CrawlResult(
            startUrl: $startUrl,
            pages: $pages,
            brokenLinks: $brokenLinks,
            externalBrokenLinks: $externalBrokenLinks,
            redirectChains: $this->filterRedirectChains($redirectChains),
            duplicateTitles: $this->duplicates($pages, 'title'),
            duplicateDescriptions: $this->duplicates($pages, 'description'),
            canonicalConflicts: $this->canonicalConflicts($pages),
            orphanPages: $this->orphanPages($pages, $incoming, $startUrl),
        );
    }

    /** @param array<string, mixed> $security */
    protected function fetch(string $url, int $timeout, string $userAgent, array $security): CrawledPage
    {
        if (! $this->isUrlAllowed($url, $security)) {
            return new CrawledPage(url: $url, status: 0, error: 'URL blocked by crawler security policy.');
        }

        $currentUrl = $url;
        $redirects = [];

        try {
            for ($attempt = 0; $attempt <= $security['max_redirects']; $attempt++) {
                $response = Http::timeout($timeout)
                    ->retry((int) $security['retry_times'], (int) $security['retry_sleep'], throw: false)
                    ->withHeaders(['User-Agent' => $userAgent])
                    ->withOptions(['allow_redirects' => false])
                    ->get($currentUrl);

                $status = $response->status();

                if (! in_array($status, [301, 302, 303, 307, 308], true)) {
                    $html = $this->limitedBody((string) $response->body(), (int) $security['max_body_kb']);

                    return $this->parse($currentUrl, $status, $html, $redirects);
                }

                $location = $response->header('Location');
                $nextUrl = is_string($location) ? $this->urls->normalize($location, $currentUrl) : null;

                if (! $nextUrl || ! $this->isUrlAllowed($nextUrl, $security)) {
                    return new CrawledPage(url: $currentUrl, status: $status, redirects: $redirects, error: 'Redirect target blocked by crawler security policy.');
                }

                if (in_array($nextUrl, $redirects, true) || $nextUrl === $url) {
                    return new CrawledPage(url: $currentUrl, status: $status, redirects: $redirects, error: 'Redirect loop detected.');
                }

                $redirects[] = $nextUrl;
                $currentUrl = $nextUrl;
            }

            return new CrawledPage(url: $currentUrl, status: 0, redirects: $redirects, error: 'Maximum redirect count exceeded.');
        } catch (\Throwable $e) {
            return new CrawledPage(url: $currentUrl, status: 0, redirects: $redirects, error: $e->getMessage());
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

        return array_values(array_filter(array_map(function (string $tag) use ($baseUrl): ?array {
            if (! preg_match('/src\s*=\s*(["\'])(.*?)\1/i', $tag, $srcMatch)) {
                return null;
            }

            $src = $this->urls->normalize($srcMatch[2], $baseUrl);

            if (! $src) {
                return null;
            }

            $alt = preg_match('/alt\s*=\s*(["\'])(.*?)\1/i', $tag, $altMatch)
                ? trim(html_entity_decode($altMatch[2]))
                : '';

            return ['src' => $src, 'alt' => $alt];
        }, $matches[0])));
    }

    protected function checkExternalLinks(array $links, int $timeout, string $userAgent, array $security, int $rateLimitMs): array
    {
        $broken = [];
        $lastRequestAt = null;

        foreach ($links as $url => $sources) {
            if (! $this->isUrlAllowed($url, $security)) {
                continue;
            }

            try {
                $this->throttle($lastRequestAt, $rateLimitMs);
                $response = Http::timeout($timeout)
                    ->retry((int) $security['retry_times'], (int) $security['retry_sleep'], throw: false)
                    ->withHeaders(['User-Agent' => $userAgent])
                    ->withOptions(['allow_redirects' => false])
                    ->head($url);
                $lastRequestAt = microtime(true);

                if ($response->status() === 405) {
                    $this->throttle($lastRequestAt, $rateLimitMs);
                    $response = Http::timeout($timeout)
                        ->retry((int) $security['retry_times'], (int) $security['retry_sleep'], throw: false)
                        ->withHeaders(['User-Agent' => $userAgent])
                        ->withOptions(['allow_redirects' => false])
                        ->get($url);
                    $lastRequestAt = microtime(true);
                }

                if ($response->status() >= 400) {
                    $broken[$url] = [
                        'status' => $response->status(),
                        'sources' => array_values(array_unique($sources)),
                    ];
                }
            } catch (\Throwable $e) {
                $broken[$url] = [
                    'status' => 0,
                    'error' => $e->getMessage(),
                    'sources' => array_values(array_unique($sources)),
                ];
            }
        }

        return $broken;
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

    /** @return array<string, mixed> */
    protected function securityOptions(array $options): array
    {
        return [
            'allow_private_networks' => (bool) ($options['allow_private_networks'] ?? config('lazy-seo.crawler.allow_private_networks', false)),
            'allowed_hosts' => array_map('strtolower', (array) ($options['allowed_hosts'] ?? config('lazy-seo.crawler.allowed_hosts', []))),
            'blocked_hosts' => array_map('strtolower', (array) ($options['blocked_hosts'] ?? config('lazy-seo.crawler.blocked_hosts', []))),
            'max_redirects' => max(0, (int) ($options['max_redirects'] ?? config('lazy-seo.crawler.max_redirects', 5))),
            'max_body_kb' => max(1, (int) ($options['max_body_kb'] ?? config('lazy-seo.crawler.max_body_kb', 1024))),
            'retry_times' => max(0, (int) ($options['retry_times'] ?? config('lazy-seo.crawler.retry_times', 1))),
            'retry_sleep' => max(0, (int) ($options['retry_sleep'] ?? config('lazy-seo.crawler.retry_sleep', 250))),
        ];
    }

    /** @param array<int, string> $exclude */
    protected function isExcluded(string $url, array $exclude): bool
    {
        foreach ($exclude as $pattern) {
            $pattern = trim((string) $pattern);

            if ($pattern === '') {
                continue;
            }

            $quoted = str_replace('\*', '.*', preg_quote($pattern, '#'));

            if (preg_match('#'.$quoted.'#i', $url) === 1) {
                return true;
            }
        }

        return false;
    }

    /** @param array<string, mixed> $security */
    protected function isUrlAllowed(string $url, array $security): bool
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true) || $host === '') {
            return false;
        }

        if ($this->hostMatches($host, (array) $security['blocked_hosts'])) {
            return false;
        }

        $allowedHosts = (array) $security['allowed_hosts'];

        if ($allowedHosts !== [] && ! $this->hostMatches($host, $allowedHosts)) {
            return false;
        }

        if ((bool) $security['allow_private_networks']) {
            return true;
        }

        return ! $this->hostResolvesToPrivateNetwork($host);
    }

    protected function hostMatches(string $host, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $pattern = strtolower(trim((string) $pattern));

            if ($pattern === '' || $pattern === '*') {
                continue;
            }

            if ($host === $pattern || str_ends_with($host, '.'.ltrim($pattern, '.'))) {
                return true;
            }
        }

        return false;
    }

    protected function hostResolvesToPrivateNetwork(string $host): bool
    {
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : gethostbynamel($host);

        if ($ips === false || $ips === []) {
            return true;
        }

        foreach ($ips as $ip) {
            if (! $this->isPublicIp($ip)) {
                return true;
            }
        }

        return false;
    }

    protected function isPublicIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    protected function limitedBody(string $body, int $maxBodyKb): string
    {
        return substr($body, 0, $maxBodyKb * 1024);
    }

    protected function throttle(?float $lastRequestAt, int $rateLimitMs): void
    {
        if ($lastRequestAt === null || $rateLimitMs <= 0) {
            return;
        }

        $elapsedMs = (microtime(true) - $lastRequestAt) * 1000;
        $sleepMs = $rateLimitMs - $elapsedMs;

        if ($sleepMs > 0) {
            usleep((int) ($sleepMs * 1000));
        }
    }

    /** @param array<string, mixed> $security @return array<int, string> */
    protected function robotsRules(string $startUrl, int $timeout, string $userAgent, array $security): array
    {
        $parts = parse_url($startUrl);
        $scheme = $parts['scheme'] ?? null;
        $host = $parts['host'] ?? null;

        if (! is_string($scheme) || ! is_string($host)) {
            return [];
        }

        $robotsUrl = $scheme.'://'.$host.'/robots.txt';

        if (! $this->isUrlAllowed($robotsUrl, $security)) {
            return [];
        }

        try {
            $response = Http::timeout($timeout)
                ->retry((int) $security['retry_times'], (int) $security['retry_sleep'], throw: false)
                ->withHeaders(['User-Agent' => $userAgent])
                ->get($robotsUrl);

            if (! $response->successful()) {
                return [];
            }

            return $this->parseRobotsDisallowRules((string) $response->body());
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array<int, string> */
    protected function parseRobotsDisallowRules(string $robotsTxt): array
    {
        $rules = [];
        $applies = false;

        foreach (preg_split('/\R/', $robotsTxt) ?: [] as $line) {
            $line = trim((string) preg_replace('/#.*/', '', $line));

            if ($line === '') {
                continue;
            }

            if (str_starts_with(strtolower($line), 'user-agent:')) {
                $agent = strtolower(trim(substr($line, strlen('user-agent:'))));
                $applies = $agent === '*';

                continue;
            }

            if ($applies && str_starts_with(strtolower($line), 'disallow:')) {
                $path = trim(substr($line, strlen('disallow:')));

                if ($path !== '') {
                    $rules[] = $path;
                }
            }
        }

        return array_values(array_unique($rules));
    }

    /** @param array<int, string> $rules */
    protected function isAllowedByRobots(string $url, array $rules): bool
    {
        if ($rules === []) {
            return true;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        foreach ($rules as $rule) {
            $pattern = '#^'.str_replace('\\*', '.*', preg_quote($rule, '#')).'#';

            if (preg_match($pattern, $path)) {
                return false;
            }
        }

        return true;
    }
}
