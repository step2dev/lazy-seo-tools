## Sitemap

Generate the configured sitemap:

```bash
php artisan lazy-seo:sitemap
```

Generate to a custom public path:

```bash
php artisan lazy-seo:sitemap --path=sitemaps/sitemap.xml
```

Warm or clear the sitemap cache:

```bash
php artisan lazy-seo:sitemap --warm
php artisan lazy-seo:sitemap:warm --clear
```

Programmatic generation:

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

Configure static URLs and models in `config/lazy-seo.php` under the `sitemap` key. When URLs exceed `chunk_size`, the package creates sitemap chunks and a sitemap index.
