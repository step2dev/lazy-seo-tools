<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Step2dev\LazySeoTools\Models\Seo;

class SeoManager extends SeoService
{
    public function analyze(Seo $seo): array
    {
        return app(SeoAnalyzerService::class)->analyze(
            (string) $seo->getTranslation('title', app()->getLocale(), false),
            (string) $seo->getTranslation('description', app()->getLocale(), false),
            (string) $seo->getTranslation('keywords', app()->getLocale(), false),
            ''
        );
    }

    public function forUrl(string $url): Seo
    {
        return Seo::query()->forUrl($url)->first() ?: $this->fallbackSeo(['url' => $url]);
    }

    public function getSeoForUrl(string $url): Seo
    {
        return $this->forUrl($url);
    }

    public function forModel(Model $model): Seo
    {
        if (method_exists($model, 'seo') && $model->relationLoaded('seo')) {
            return $model->seo ?: $this->fallbackSeo();
        }

        if (method_exists($model, 'seo')) {
            return $model->seo()->first() ?: $this->fallbackSeo();
        }

        return $this->fallbackSeo();
    }

    public function current(): Seo
    {
        return $this->forUrl(request()->path());
    }

    public function render(?Seo $seo = null, array $overrides = []): HtmlString
    {
        $data = $this->toArray($seo ?? $this->current(), $overrides);
        $robots = implode(', ', $data['robots']);

        $tags = array_filter([
            '<title>'.e($data['title']).'</title>',
            '<meta name="description" content="'.e($data['description']).'">',
            $data['keywords'] !== '' ? '<meta name="keywords" content="'.e($data['keywords']).'">' : null,
            '<meta name="robots" content="'.e($robots).'">',
            $data['canonical_url'] ? '<link rel="canonical" href="'.e($data['canonical_url']).'">' : null,
            '<meta property="og:title" content="'.e($data['title']).'">',
            '<meta property="og:description" content="'.e($data['description']).'">',
            '<meta property="og:type" content="'.e($data['type']).'">',
            '<meta property="og:url" content="'.e($data['url']).'">',
            $data['image'] ? '<meta property="og:image" content="'.e($data['image']).'">' : null,
            '<meta name="twitter:card" content="summary_large_image">',
            '<meta name="twitter:title" content="'.e($data['title']).'">',
            '<meta name="twitter:description" content="'.e($data['description']).'">',
        ]);

        return new HtmlString(implode("\n", $tags));
    }

    public function renderMetaTags(?Seo $seo = null, array $overrides = []): HtmlString
    {
        return $this->render($seo, $overrides);
    }

    public function toArray(Seo $seo, array $overrides = []): array
    {
        $locale = app()->getLocale();
        $defaults = config('lazy-seo.defaults', []);

        $data = [
            'url' => request()?->fullUrl() ?? url('/'),
            'title' => $this->translated($seo, 'title', $locale) ?: ($defaults['title'] ?? config('app.name')),
            'description' => $this->translated($seo, 'description', $locale) ?: ($defaults['description'] ?? ''),
            'keywords' => $this->translated($seo, 'keywords', $locale) ?: ($defaults['keywords'] ?? ''),
            'canonical_url' => $seo->canonical_url ?: ($defaults['canonical_url'] ?? null),
            'robots' => $seo->robots ?: ($defaults['robots'] ?? ['index', 'follow']),
            'image' => $defaults['image'] ?? null,
            'type' => $defaults['type'] ?? 'website',
        ];

        if (! $seo->indexable) {
            $data['robots'] = ['noindex', 'nofollow'];
        }

        return array_replace($data, array_filter($overrides, static fn ($value) => $value !== null));
    }

    protected function fallbackSeo(array $attributes = []): Seo
    {
        return new Seo(array_replace([
            'indexable' => true,
            'robots' => config('lazy-seo.defaults.robots', ['index', 'follow']),
        ], $attributes));
    }

    protected function translated(Seo $seo, string $field, string $locale): string
    {
        $value = $seo->getTranslation($field, $locale, false);

        return is_string($value) ? $value : '';
    }
}
