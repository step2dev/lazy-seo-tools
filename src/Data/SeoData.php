<?php

namespace Step2dev\LazySeoTools\Data;

use Step2dev\LazySeoTools\Models\Seo;

final readonly class SeoData
{
    public function __construct(
        public string $url,
        public string $title,
        public string $description = '',
        public string $keywords = '',
        public ?string $canonicalUrl = null,
        public array $robots = ['index', 'follow'],
        public ?string $image = null,
        public string $type = 'website',
        public array $extra = [],
    ) {}

    public static function defaults(array $overrides = []): self
    {
        $defaults = config('lazy-seo.defaults', []);

        return self::fromArray(array_replace([
            'url' => request()?->fullUrl() ?? url('/'),
            'title' => (string) ($defaults['title'] ?? config('app.name', 'Laravel')),
            'description' => (string) ($defaults['description'] ?? ''),
            'keywords' => (string) ($defaults['keywords'] ?? ''),
            'canonicalUrl' => $defaults['canonical_url'] ?? null,
            'robots' => $defaults['robots'] ?? ['index', 'follow'],
            'image' => $defaults['image'] ?? null,
            'type' => (string) ($defaults['type'] ?? 'website'),
        ], self::normalizeKeys($overrides)));
    }

    public static function fromSeo(?Seo $seo = null, array $overrides = []): self
    {
        if (! $seo) {
            return self::defaults($overrides);
        }

        $locale = app()->getLocale();
        $defaults = config('lazy-seo.defaults', []);
        $robots = $seo->indexable !== false
            ? ($seo->robots ?: ($defaults['robots'] ?? ['index', 'follow']))
            : ['noindex', 'nofollow'];

        return self::defaults(array_replace([
            'url' => $seo->url ?: (request()?->fullUrl() ?? url('/')),
            'title' => self::translated($seo, 'title', $locale) ?: null,
            'description' => self::translated($seo, 'description', $locale) ?: null,
            'keywords' => self::translated($seo, 'keywords', $locale) ?: null,
            'canonicalUrl' => $seo->canonical_url,
            'robots' => $robots,
        ], self::normalizeKeys($overrides)));
    }

    public static function fromArray(array $data): self
    {
        $data = self::normalizeKeys($data);
        $known = ['url', 'title', 'description', 'keywords', 'canonicalUrl', 'robots', 'image', 'type'];
        $extra = array_diff_key($data, array_flip($known));

        return new self(
            url: (string) ($data['url'] ?? (request()?->fullUrl() ?? url('/'))),
            title: (string) ($data['title'] ?? config('app.name', 'Laravel')),
            description: (string) ($data['description'] ?? ''),
            keywords: is_array($data['keywords'] ?? null) ? implode(', ', $data['keywords']) : (string) ($data['keywords'] ?? ''),
            canonicalUrl: filled($data['canonicalUrl'] ?? null) ? (string) $data['canonicalUrl'] : null,
            robots: array_values(array_filter((array) ($data['robots'] ?? ['index', 'follow']))),
            image: filled($data['image'] ?? null) ? (string) $data['image'] : null,
            type: (string) ($data['type'] ?? 'website'),
            extra: $extra,
        );
    }

    public function merge(array $overrides): self
    {
        return self::fromArray(array_replace($this->toArray(), self::normalizeKeys($overrides)));
    }

    public function with(array $overrides): self
    {
        return $this->merge($overrides);
    }

    public function toArray(): array
    {
        return array_replace([
            'url' => $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'canonical_url' => $this->canonicalUrl,
            'canonicalUrl' => $this->canonicalUrl,
            'robots' => $this->robots,
            'image' => $this->image,
            'type' => $this->type,
        ], $this->extra);
    }

    public function robotsContent(): string
    {
        return implode(', ', $this->robots);
    }

    private static function translated(?Seo $seo, string $field, string $locale): string
    {
        if (! $seo) {
            return '';
        }

        $value = $seo->getTranslation($field, $locale, false);

        return is_string($value) ? $value : '';
    }

    private static function normalizeKeys(array $data): array
    {
        if (array_key_exists('canonical_url', $data)) {
            if ($data['canonical_url'] !== null) {
                $data['canonicalUrl'] = $data['canonical_url'];
            }

            unset($data['canonical_url']);
        }

        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }
}
