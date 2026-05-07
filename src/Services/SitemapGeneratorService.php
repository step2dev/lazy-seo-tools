<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Step2dev\LazySeoTools\Models\Seo;

class SitemapGeneratorService
{
    public function generate(?array $items = null, ?string $path = null): string
    {
        $path ??= config('lazy-seo.sitemap.path', 'sitemap.xml');
        $items ??= $this->itemsFromSeoTable();

        $sitemap = Sitemap::create();

        foreach ($items as $item) {
            $loc = $item['loc'] ?? $item['url'] ?? null;

            if (! $loc) {
                continue;
            }

            $sitemap->add(
                Url::create($this->absoluteUrl($loc))
                    ->setLastModificationDate($this->lastModified($item['lastmod'] ?? null))
                    ->setChangeFrequency($item['freq'] ?? $item['changefreq'] ?? config('lazy-seo.sitemap.default_change_frequency', 'weekly'))
                    ->setPriority((float) ($item['priority'] ?? config('lazy-seo.sitemap.default_priority', 0.8)))
            );
        }

        $target = public_path($path);
        $directory = dirname($target);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $sitemap->writeToFile($target);

        return $target;
    }

    public function cached(?array $items = null, ?string $cacheKey = null, ?int $minutes = null): string
    {
        $cacheKey ??= config('lazy-seo.sitemap.cache_key', 'lazy-seo.sitemap');
        $minutes ??= (int) config('lazy-seo.sitemap.cache_minutes', 60);

        return Cache::remember($cacheKey, now()->addMinutes($minutes), fn () => $this->generate($items));
    }

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
}
