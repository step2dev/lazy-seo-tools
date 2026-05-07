<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Step2dev\LazySeoTools\Models\Seo;

class SitemapGeneratorService
{
    /**
     * @param array<int, array<string, mixed>>|null $items
     */
    public function generate(?array $items = null, ?string $path = null): string
    {
        $result = $this->generateFiles($items, $path);

        return $result['index'] ?? $result['files'][0];
    }

    /**
     * @param array<int, array<string, mixed>>|null $items
     * @return array{index?: string, files: array<int, string>}
     */
    public function generateFiles(?array $items = null, ?string $path = null): array
    {
        $path ??= config('lazy-seo.sitemap.path', 'sitemap.xml');
        $items ??= $this->items();

        $items = $this->filterItems($items);
        $chunkSize = max(1, min(50000, (int) config('lazy-seo.sitemap.chunk_size', 50000)));
        $gzip = (bool) config('lazy-seo.sitemap.gzip', false);
        $chunks = array_chunk($items, $chunkSize);

        if ($chunks === []) {
            $chunks = [[]];
        }

        $files = [];
        $multiple = count($chunks) > 1 || (bool) config('lazy-seo.sitemap.force_index', false);

        foreach ($chunks as $index => $chunk) {
            $chunkPath = $multiple
                ? $this->chunkPath($path, $index + 1, $gzip)
                : $this->normalizeGzipPath($path, $gzip);

            $files[] = $this->writePublicFile($chunkPath, $this->sitemapXml($chunk), $gzip);
        }

        if (! $multiple) {
            return ['files' => $files];
        }

        $indexPath = $this->normalizeGzipPath(config('lazy-seo.sitemap.index_path', $path), $gzip);
        $indexFile = $this->writePublicFile($indexPath, $this->sitemapIndexXml($files), $gzip);

        return [
            'index' => $indexFile,
            'files' => $files,
        ];
    }

    /**
     * @param array<int, array<string, mixed>>|null $items
     */
    public function cached(?array $items = null, ?string $cacheKey = null, ?int $minutes = null): string
    {
        $cacheKey ??= config('lazy-seo.sitemap.cache_key', 'lazy-seo.sitemap');
        $minutes ??= (int) config('lazy-seo.sitemap.cache_minutes', 60);

        return Cache::remember($cacheKey, now()->addMinutes($minutes), fn () => $this->generate($items));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function items(): array
    {
        return array_values(array_merge(
            $this->itemsFromSeoTable(),
            $this->itemsFromConfiguredModels(),
            config('lazy-seo.sitemap.static_urls', [])
        ));
    }

    /** @return array<int, array<string, mixed>> */
    public function itemsFromSeoTable(): array
    {
        return Seo::query()
            ->whereNotNull('url')
            ->where('indexable', true)
            ->get(['url', 'updated_at'])
            ->map(fn (Seo $seo) => [
                'loc' => $seo->url,
                'lastmod' => $seo->updated_at,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function itemsFromConfiguredModels(): array
    {
        $items = [];

        foreach ((array) config('lazy-seo.sitemap.models', []) as $class => $sourceConfig) {
            if (is_int($class)) {
                $class = $sourceConfig;
                $sourceConfig = [];
            }

            if (! is_string($class) || ! class_exists($class) || ! is_subclass_of($class, Model::class)) {
                continue;
            }

            /** @var Model $model */
            $model = new $class();
            $query = $class::query();

            if (($sourceConfig['enabled'] ?? true) === false) {
                continue;
            }

            $scope = $sourceConfig['scope'] ?? null;
            if (is_string($scope) && method_exists($query, $scope)) {
                $query->{$scope}();
            }

            $query->chunkById((int) ($sourceConfig['chunk'] ?? 500), function ($models) use (&$items, $sourceConfig): void {
                foreach ($models as $model) {
                    $loc = $this->modelUrl($model, $sourceConfig);

                    if (! $loc) {
                        continue;
                    }

                    $items[] = [
                        'loc' => $loc,
                        'lastmod' => $model->{$sourceConfig['lastmod_column'] ?? 'updated_at'} ?? null,
                        'changefreq' => $sourceConfig['changefreq'] ?? config('lazy-seo.sitemap.default_change_frequency', 'weekly'),
                        'priority' => $sourceConfig['priority'] ?? config('lazy-seo.sitemap.default_priority', 0.8),
                    ];
                }
            }, $model->getKeyName());
        }

        return $items;
    }

    /** @param array<string, mixed> $sourceConfig */
    protected function modelUrl(Model $model, array $sourceConfig): ?string
    {
        $callback = $sourceConfig['url'] ?? null;

        if ($callback instanceof \Closure) {
            return $callback($model);
        }

        if (is_string($callback) && method_exists($model, $callback)) {
            return $model->{$callback}();
        }

        if (method_exists($model, 'getSeoUrl')) {
            return $model->getSeoUrl();
        }

        if (method_exists($model, 'getUrlAttribute')) {
            return $model->url;
        }

        return null;
    }

    /** @param array<int, array<string, mixed>> $items */
    protected function filterItems(array $items): array
    {
        $exclude = array_map(fn (string $path): string => trim($path, '/'), (array) config('lazy-seo.sitemap.exclude', []));
        $seen = [];
        $filtered = [];

        foreach ($items as $item) {
            $loc = $item['loc'] ?? $item['url'] ?? null;

            if (! is_string($loc) || $loc === '') {
                continue;
            }

            $absolute = $this->absoluteUrl($loc);
            $path = trim(parse_url($absolute, PHP_URL_PATH) ?: '', '/');

            if ($this->isExcluded($path, $exclude) || isset($seen[$absolute])) {
                continue;
            }

            $seen[$absolute] = true;
            $item['loc'] = $absolute;
            $filtered[] = $item;
        }

        return $filtered;
    }

    /** @param array<int, string> $exclude */
    protected function isExcluded(string $path, array $exclude): bool
    {
        foreach ($exclude as $pattern) {
            $regex = '#^'.str_replace('\\*', '.*', preg_quote($pattern, '#')).'$#u';

            if ((bool) preg_match($regex, $path)) {
                return true;
            }
        }

        return false;
    }

    /** @param array<int, array<string, mixed>> $items */
    protected function sitemapXml(array $items): string
    {
        $urls = array_map(function (array $item): string {
            $xml = '    <url>'.PHP_EOL;
            $xml .= '        <loc>'.e($item['loc']).'</loc>'.PHP_EOL;
            $xml .= '        <lastmod>'.$this->lastModified($item['lastmod'] ?? null)->toAtomString().'</lastmod>'.PHP_EOL;
            $xml .= '        <changefreq>'.e($item['freq'] ?? $item['changefreq'] ?? config('lazy-seo.sitemap.default_change_frequency', 'weekly')).'</changefreq>'.PHP_EOL;
            $xml .= '        <priority>'.number_format((float) ($item['priority'] ?? config('lazy-seo.sitemap.default_priority', 0.8)), 1, '.', '').'</priority>'.PHP_EOL;
            $xml .= '    </url>';

            return $xml;
        }, $items);

        return '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL
            .implode(PHP_EOL, $urls).PHP_EOL
            .'</urlset>'.PHP_EOL;
    }

    /** @param array<int, string> $files */
    protected function sitemapIndexXml(array $files): string
    {
        $entries = array_map(function (string $file): string {
            $relative = str($file)->after(public_path().DIRECTORY_SEPARATOR)->replace(DIRECTORY_SEPARATOR, '/')->toString();

            return '    <sitemap>'.PHP_EOL
                .'        <loc>'.e(url($relative)).'</loc>'.PHP_EOL
                .'        <lastmod>'.now()->toAtomString().'</lastmod>'.PHP_EOL
                .'    </sitemap>';
        }, $files);

        return '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL
            .'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL
            .implode(PHP_EOL, $entries).PHP_EOL
            .'</sitemapindex>'.PHP_EOL;
    }

    protected function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return url($url);
    }

    protected function lastModified(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        return $value ? Carbon::parse($value) : now();
    }

    protected function writePublicFile(string $path, string $contents, bool $gzip): string
    {
        $target = public_path($path);

        File::ensureDirectoryExists(dirname($target));
        File::put($target, $gzip ? gzencode($contents) : $contents);

        return $target;
    }

    protected function chunkPath(string $path, int $index, bool $gzip): string
    {
        $path = preg_replace('/\.gz$/', '', $path) ?: $path;
        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'xml';
        $base = substr($path, 0, -strlen($extension) - 1);

        return $this->normalizeGzipPath($base.'-'.$index.'.'.$extension, $gzip);
    }

    protected function normalizeGzipPath(string $path, bool $gzip): string
    {
        if (! $gzip) {
            return preg_replace('/\.gz$/', '', $path) ?: $path;
        }

        return str_ends_with($path, '.gz') ? $path : $path.'.gz';
    }
}
