<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Contracts\Support\Arrayable;

class SchemaService
{
    public function make(string $type, array $data = []): array
    {
        $type = $this->normalizeType($type);

        return $this->{$this->methodName($type)}($data);
    }

    public function script(string $type, array $data = []): string
    {
        return '<script type="application/ld+json">'.$this->toJson($this->make($type, $data)).'</script>';
    }

    public function toJson(array|Arrayable $schema): string
    {
        if ($schema instanceof Arrayable) {
            $schema = $schema->toArray();
        }

        return json_encode($this->clean($schema), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '{}';
    }

    public function webPage(array $data = []): array
    {
        return $this->base('WebPage', [
            'name' => $data['name'] ?? $data['title'] ?? config('lazy-seo.defaults.title', config('app.name')),
            'description' => $data['description'] ?? config('lazy-seo.defaults.description', ''),
            'url' => $data['url'] ?? request()?->fullUrl(),
        ], $data);
    }

    public function article(array $data = []): array
    {
        return $this->base($data['type'] ?? 'Article', [
            'headline' => $data['headline'] ?? $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null,
            'datePublished' => $data['date_published'] ?? $data['datePublished'] ?? null,
            'dateModified' => $data['date_modified'] ?? $data['dateModified'] ?? null,
            'author' => $this->personOrOrganization($data['author'] ?? null),
            'publisher' => $this->organization($data['publisher'] ?? []),
            'mainEntityOfPage' => $data['url'] ?? request()?->fullUrl(),
        ], $data, [
            'author',
            'publisher',
            'headline',
            'title',
            'date_published',
            'date_modified',
        ]);
    }

    public function blogPosting(array $data = []): array
    {
        return $this->article(array_replace(['type' => 'BlogPosting'], $data));
    }

    public function product(array $data = []): array
    {
        return $this->base('Product', [
            'name' => $data['name'] ?? $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null,
            'sku' => $data['sku'] ?? null,
            'brand' => isset($data['brand']) ? $this->organization(['name' => $data['brand']]) : null,
            'offers' => $this->offers($data['offers'] ?? $data),
        ], $data, [
            'brand',
            'offers',
            'title',
            'price',
            'price_currency',
            'priceCurrency',
            'availability',
        ]);
    }

    public function organization(array $data = []): array
    {
        return $this->base($data['type'] ?? 'Organization', [
            'name' => $data['name'] ?? config('app.name'),
            'url' => $data['url'] ?? config('app.url'),
            'logo' => $data['logo'] ?? config('lazy-seo.schema.organization.logo'),
            'sameAs' => $data['same_as'] ?? $data['sameAs'] ?? config('lazy-seo.schema.organization.same_as', []),
        ], $data, ['same_as']);
    }

    public function localBusiness(array $data = []): array
    {
        return $this->base('LocalBusiness', [
            'name' => $data['name'] ?? config('app.name'),
            'url' => $data['url'] ?? config('app.url'),
            'telephone' => $data['telephone'] ?? $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'openingHours' => $data['opening_hours'] ?? $data['openingHours'] ?? null,
        ], $data);
    }

    public function webSite(array $data = []): array
    {
        return $this->base('WebSite', [
            'name' => $data['name'] ?? config('app.name'),
            'url' => $data['url'] ?? config('app.url'),
            'potentialAction' => $this->searchAction($data['search_url'] ?? $data['searchUrl'] ?? null),
        ], $data);
    }

    public function breadcrumbList(array $items = []): array
    {
        if (array_key_exists('items', $items)) {
            $items = $items['items'];
        }

        return $this->base('BreadcrumbList', [
            'itemListElement' => collect($items)->values()->map(function (array|string $item, int $index): array {
                $name = is_array($item) ? ($item['name'] ?? $item['title'] ?? '') : (string) $item;
                $url = is_array($item) ? ($item['url'] ?? $item['item'] ?? null) : null;

                return $this->clean([
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $name,
                    'item' => $url,
                ]);
            })->all(),
        ]);
    }

    public function faqPage(array $items = []): array
    {
        if (array_key_exists('items', $items)) {
            $items = $items['items'];
        }

        return $this->base('FAQPage', [
            'mainEntity' => collect($items)->map(function (array $item): array {
                return $this->clean([
                    '@type' => 'Question',
                    'name' => $item['question'] ?? $item['name'] ?? null,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'] ?? $item['text'] ?? null,
                    ],
                ]);
            })->values()->all(),
        ]);
    }

    protected function offers(array $data): ?array
    {
        if (! isset($data['price']) && ! isset($data['priceCurrency']) && ! isset($data['price_currency'])) {
            return null;
        }

        return $this->clean([
            '@type' => 'Offer',
            'price' => $data['price'] ?? null,
            'priceCurrency' => $data['price_currency'] ?? $data['priceCurrency'] ?? 'USD',
            'availability' => $data['availability'] ?? 'https://schema.org/InStock',
            'url' => $data['url'] ?? request()?->fullUrl(),
        ]);
    }

    protected function searchAction(?string $searchUrl): ?array
    {
        if (! $searchUrl) {
            return null;
        }

        return [
            '@type' => 'SearchAction',
            'target' => $searchUrl,
            'query-input' => 'required name=search_term_string',
        ];
    }

    protected function personOrOrganization(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $this->base($value['type'] ?? 'Person', ['name' => $value['name'] ?? null], $value);
        }

        return ['@type' => 'Person', 'name' => (string) $value];
    }

    /**
     * @param  array<int, string>  $consumed
     */
    protected function base(string $type, array $schema, array $data = [], array $consumed = []): array
    {
        foreach (array_merge(['type', 'items', 'search_url', 'searchUrl'], $consumed) as $key) {
            unset($data[$key]);
        }

        return $this->clean(array_replace([
            '@context' => 'https://schema.org',
            '@type' => $type,
        ], $schema, $data));
    }

    protected function clean(array $data): array
    {
        return array_filter($data, function (mixed $value): bool {
            if ($value === null || $value === '') {
                return false;
            }

            return ! (is_array($value) && $value === []);
        });
    }

    protected function normalizeType(string $type): string
    {
        return str($type)->replace(['-', '_'], '')->lower()->toString();
    }

    protected function methodName(string $type): string
    {
        return match ($type) {
            'article' => 'article',
            'blogposting', 'blogpost' => 'blogPosting',
            'product' => 'product',
            'organization' => 'organization',
            'localbusiness' => 'localBusiness',
            'website' => 'webSite',
            'breadcrumblist', 'breadcrumbs' => 'breadcrumbList',
            'faqpage', 'faq' => 'faqPage',
            default => 'webPage',
        };
    }
}
