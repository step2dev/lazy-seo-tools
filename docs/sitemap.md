# Sitemap

The package generates sitemap files from URL SEO records, static URLs and configured model sources.

## Commands

Generate the configured sitemap:

```bash
php artisan lazy-seo:sitemap
```

Generate to a custom public path:

```bash
php artisan lazy-seo:sitemap --path=sitemaps/sitemap.xml
```

Use cache:

```bash
php artisan lazy-seo:sitemap --cached
php artisan lazy-seo:sitemap --warm
php artisan lazy-seo:sitemap --clear-cache
```

Warm cache through the dedicated command:

```bash
php artisan lazy-seo:sitemap:warm
php artisan lazy-seo:sitemap:warm --clear
```

Output generated paths as JSON:

```bash
php artisan lazy-seo:sitemap --json
```

## Programmatic generation

```php
use Step2dev\LazySeoTools\Services\SitemapGeneratorService;

$path = app(SitemapGeneratorService::class)->generate([
    [
        'loc' => '/about',
        'lastmod' => now(),
        'changefreq' => 'weekly',
        'priority' => 0.8,
    ],
]);
```

## Configuration

Configure static URLs and models in `config/lazy-seo.php` under the `sitemap` key:

```php
'sitemap' => [
    'path' => 'sitemap.xml',
    'index_path' => 'sitemap.xml',
    'chunk_size' => 50000,
    'gzip' => false,
    'force_index' => false,
    'exclude' => [
        'admin/*',
        'nova/*',
        'horizon/*',
        'telescope/*',
    ],
    'static_urls' => [
        [
            'loc' => '/',
            'changefreq' => 'daily',
            'priority' => 1.0,
        ],
    ],
    'models' => [
        App\Models\Post::class => [
            'enabled' => true,
            'url' => 'getSeoUrl',
            'scope' => 'published',
            'lastmod_column' => 'updated_at',
            'changefreq' => 'weekly',
            'priority' => 0.8,
            'images' => 'getSeoImages',
            'alternates' => 'getSeoAlternates',
        ],
    ],
],
```

When URL count exceeds `chunk_size`, the package writes sitemap chunks and a sitemap index automatically.
