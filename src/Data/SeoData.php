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
    ) {}

    public static function fromSeo(Seo $seo, array $overrides = []): self
    {
        $locale = app()->getLocale();
        $defaults = config('lazy-seo.defaults', []);

        $robots = $seo->indexable
            ? ($seo->robots ?: ($defaults['robots'] ?? ['index', 'follow']))
            : ['noindex', 'nofollow'];

        $data = [
            'url' => request()?->fullUrl() ?? url('/'),
            'title' => self::translated($seo, 'title', $locale) ?: (string) ($defaults['title'] ?? config('app.name')),
            'description' => self::translated($seo, 'description', $locale) ?: (string) ($defaults['description'] ?? ''),
            'keywords' => self::translated($seo, 'keywords', $locale) ?: (string) ($defaults['keywords'] ?? ''),
            'canonicalUrl' => $seo->canonical_url ?: ($defaults['canonical_url'] ?? null),
            'robots' => $robots,
            'image' => $defaults['image'] ?? null,
            'type' => (string) ($defaults['type'] ?? 'website'),
        ];

        $data = array_replace($data, array_filter($overrides, static fn (mixed $value): bool => $value !== null));

        return new self(
            url: (string) $data['url'],
            title: (string) $data['title'],
            description: (string) $data['description'],
            keywords: (string) $data['keywords'],
            canonicalUrl: $data['canonicalUrl'] ? (string) $data['canonicalUrl'] : null,
            robots: array_values((array) $data['robots']),
            image: $data['image'] ? (string) $data['image'] : null,
            type: (string) $data['type'],
        );
    }

    public function toArray(): array
    {
        return [
            'url' => $this->url,
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'canonical_url' => $this->canonicalUrl,
            'robots' => $this->robots,
            'image' => $this->image,
            'type' => $this->type,
        ];
    }

    private static function translated(Seo $seo, string $field, string $locale): string
    {
        $value = $seo->getTranslation($field, $locale, false);

        return is_string($value) ? $value : '';
    }
}
