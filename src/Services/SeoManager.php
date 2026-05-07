<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;
use Step2dev\LazySeoTools\Contracts\SeoResolver;
use Step2dev\LazySeoTools\Data\SeoData;
use Step2dev\LazySeoTools\Models\Seo;
use Step2dev\LazySeoTools\Models\SeoTemplate;

class SeoManager extends SeoService implements SeoResolver
{
    protected array $fluent = [];

    protected ?SeoData $resolvedData = null;

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
        $cacheKey = $this->cacheKey('url', $url);

        return $this->remember($cacheKey, fn (): Seo => Seo::query()->forUrl($url)->first() ?: $this->fallbackSeo(['url' => $url]));
    }

    public function getSeoForUrl(string $url): Seo
    {
        return $this->forUrl($url);
    }

    public function forModel(Model $model): Seo
    {
        if (! method_exists($model, 'seo')) {
            return $this->fallbackSeo();
        }

        if ($model->relationLoaded('seo')) {
            return $model->seo ?: $this->fallbackSeo();
        }

        $key = $model->getKey();

        if (! $key) {
            return $this->fallbackSeo();
        }

        return $this->remember($this->cacheKey('model', $model::class.':'.$key), fn (): Seo => $model->seo()->first() ?: $this->fallbackSeo());
    }

    public function current(): Seo
    {
        return $this->forUrl(request()->path());
    }

    public function resolve(?Model $model = null, ?string $url = null, array $overrides = []): SeoData
    {
        $data = SeoData::defaults();

        // Priority: config defaults -> route/url SEO -> model SEO -> template/fluent/manual overrides.
        if ($url !== null) {
            $data = $data->merge($this->toDataArray($this->forUrl($url)));
        }

        if ($model !== null) {
            $data = $data->merge($this->toDataArray($this->forModel($model)));
        }

        $data = $data->merge($this->normalizeKeys($this->fluent));
        $data = $data->merge($this->normalizeKeys($overrides));

        $this->resolvedData = $data;

        return $data;
    }

    public function make(array $attributes = []): SeoData
    {
        return $this->resolve(overrides: $attributes);
    }

    public function title(string $title): self
    {
        $this->fluent['title'] = $title;

        return $this;
    }

    public function description(string $description): self
    {
        $this->fluent['description'] = $description;

        return $this;
    }

    public function keywords(string|array $keywords): self
    {
        $this->fluent['keywords'] = is_array($keywords) ? implode(', ', $keywords) : $keywords;

        return $this;
    }

    public function canonical(string $url): self
    {
        $this->fluent['canonicalUrl'] = $url;

        return $this;
    }

    public function url(string $url): self
    {
        $this->fluent['url'] = $url;

        return $this;
    }

    public function image(string $url): self
    {
        $this->fluent['image'] = $url;

        return $this;
    }

    public function type(string $type): self
    {
        $this->fluent['type'] = $type;

        return $this;
    }

    public function robots(array|string $robots): self
    {
        $this->fluent['robots'] = is_string($robots)
            ? array_map('trim', explode(',', $robots))
            : $robots;

        return $this;
    }

    public function noIndex(): self
    {
        return $this->robots(['noindex', 'nofollow']);
    }

    public function template(string $name, array $context = []): self
    {
        if (! config('lazy-seo.templates.enabled', true)) {
            return $this;
        }

        $template = SeoTemplate::query()->enabled()->where('name', $name)->first();

        if (! $template) {
            return $this;
        }

        $this->fluent = array_replace($this->fluent, $this->templateToArray($template, $context));

        return $this;
    }

    public function reset(): self
    {
        $this->fluent = [];
        $this->resolvedData = null;

        return $this;
    }

    public function data(?Seo $seo = null, array $overrides = []): SeoData
    {
        if ($seo !== null) {
            $data = SeoData::fromSeo($seo)->merge($this->normalizeKeys($this->fluent))->merge($this->normalizeKeys($overrides));
            $this->resolvedData = $data;

            return $data;
        }

        return $this->resolve(overrides: $overrides);
    }

    public function toArray(?Seo $seo = null, array $overrides = []): array
    {
        return $this->data($seo, $overrides)->toArray();
    }

    public function render(?Seo $seo = null, array $overrides = []): HtmlString
    {
        $data = $seo || $overrides ? $this->data($seo, $overrides) : ($this->resolvedData ?: $this->data());

        return new HtmlString(implode("\n", array_filter([
            '<title>'.e($data->title).'</title>',
            '<meta name="description" content="'.e($data->description).'">',
            $data->keywords !== '' ? '<meta name="keywords" content="'.e($data->keywords).'">' : null,
            '<meta name="robots" content="'.e($data->robotsContent()).'">',
            $data->canonicalUrl ? '<link rel="canonical" href="'.e($data->canonicalUrl).'">' : null,
            '<meta property="og:title" content="'.e($data->title).'">',
            '<meta property="og:description" content="'.e($data->description).'">',
            '<meta property="og:type" content="'.e($data->type).'">',
            '<meta property="og:url" content="'.e($data->url).'">',
            $data->image ? '<meta property="og:image" content="'.e($data->image).'">' : null,
            '<meta name="twitter:card" content="summary_large_image">',
            '<meta name="twitter:title" content="'.e($data->title).'">',
            '<meta name="twitter:description" content="'.e($data->description).'">',
            $data->image ? '<meta name="twitter:image" content="'.e($data->image).'">' : null,
        ])));
    }

    public function renderMetaTags(?Seo $seo = null, array $overrides = []): HtmlString
    {
        return $this->render($seo, $overrides);
    }

    protected function fallbackSeo(array $attributes = []): Seo
    {
        return new Seo(array_replace([
            'indexable' => true,
            'robots' => config('lazy-seo.defaults.robots', ['index', 'follow']),
        ], $attributes));
    }

    protected function toDataArray(Seo $seo): array
    {
        return SeoData::fromSeo($seo)->toArray();
    }

    protected function templateToArray(SeoTemplate $template, array $context): array
    {
        $locale = app()->getLocale();
        $data = [];

        foreach (['title', 'description', 'keywords'] as $field) {
            $value = $template->getTranslation($field, $locale, false);

            if (is_string($value) && $value !== '') {
                $data[$field] = $this->replacePlaceholders($value, $context);
            }
        }

        foreach ((array) $template->payload as $key => $value) {
            if (is_scalar($value)) {
                $data[$key] = $this->replacePlaceholders((string) $value, $context);
            }
        }

        return $this->normalizeKeys($data);
    }

    protected function replacePlaceholders(string $value, array $context): string
    {
        $context = array_replace([
            'site_name' => config('app.name'),
            'locale' => app()->getLocale(),
        ], $context);

        foreach ($context as $key => $replacement) {
            if (is_scalar($replacement)) {
                $value = str_replace('{'.$key.'}', (string) $replacement, $value);
            }
        }

        return $value;
    }

    protected function normalizeKeys(array $data): array
    {
        if (array_key_exists('canonical_url', $data)) {
            $data['canonicalUrl'] = $data['canonical_url'];
            unset($data['canonical_url']);
        }

        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }

    protected function remember(string $key, callable $callback): mixed
    {
        $minutes = (int) config('lazy-seo.cache.resolved_minutes', 0);

        if ($minutes <= 0) {
            return $callback();
        }

        return Cache::remember($key, now()->addMinutes($minutes), $callback);
    }

    protected function cacheKey(string $type, string $value): string
    {
        return 'lazy-seo.resolved.'.sha1($type.':'.$value.':'.app()->getLocale());
    }
}
