# Lazy SEO Tools

Laravel SEO toolkit built on `spatie/laravel-package-tools`.

## Install

```bash
composer require step2dev/lazy-seo-tools
php artisan vendor:publish --tag="lazy-seo-config"
php artisan migrate
```

## Configurable tables

Table names are configured directly in `config/lazy-seo.php` and intentionally do not use `env()`:

```php
'tables' => [
    'seo' => 'seo',
    'seo_redirects' => 'seo_redirects',
    'seo_templates' => 'seo_templates',
],
```

Runtime options like routes, sitemap cache, redirects, OG image settings, webhooks and AI token may use `env()` inside config.

## Fluent API

```php
seo()
    ->title('Page title')
    ->description('Page description')
    ->canonical('/page')
    ->image('/storage/og/page.jpg')
    ->type('article');
```

Render in Blade:

```blade
{!! seo()->renderMetaTags() !!}
```

## Blade components

```blade
<x-lazy-seo-meta />
<x-lazy-seo-title />
<x-lazy-seo-og />
<x-lazy-seo-twitter />
<x-lazy-seo-jsonld :data="$schema" />
```

Anonymous package views are also available:

```blade
<x-seo::meta />
<x-seo::og />
<x-seo::twitter />
<x-seo::jsonld :data="$schema" />
```

## Model SEO

```php
use Step2dev\LazySeoTools\Concerns\HasSeo;

class Post extends Model
{
    use HasSeo;
}
```

```php
$post->updateSeo([
    'title' => ['en' => 'Post title'],
    'description' => ['en' => 'Post description'],
]);

$data = $post->resolvedSeo();
```

## Resolver priority

`SeoManager::resolve()` uses this order:

1. config defaults;
2. URL SEO;
3. model SEO;
4. templates/fluent API/manual overrides.

```php
$data = seo()
    ->title('Manual title')
    ->resolve(model: $post, url: '/blog/post');
```

## Sitemap

```bash
php artisan lazy-seo:sitemap
```

## Redirects

Enable middleware manually in your app if needed:

```php
use Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects;
```

Supports exact URLs, wildcard URLs, 301/302/307/308, 410 Gone, hit counters and loop protection.

## Tests

```bash
composer install
vendor/bin/pest
```

## Sitemap v2

Generate sitemap files from SEO records, static URLs, and configured Eloquent models:

```bash
php artisan lazy-seo:sitemap
php artisan lazy-seo:sitemap --path=sitemaps/sitemap.xml
```

Config options:

```php
'sitemap' => [
    'path' => 'sitemap.xml',
    'index_path' => 'sitemap.xml',
    'chunk_size' => 50000,
    'gzip' => false,
    'force_index' => false,
    'exclude' => ['admin/*'],
    'static_urls' => [
        ['loc' => '/', 'changefreq' => 'daily', 'priority' => 1.0],
    ],
    'models' => [
        App\Models\Post::class => [
            'enabled' => true,
            'url' => 'getSeoUrl',
            'scope' => 'published',
            'lastmod_column' => 'updated_at',
            'changefreq' => 'weekly',
            'priority' => 0.8,
        ],
    ],
],
```

When the URL count exceeds `chunk_size`, the package writes sitemap chunks and a sitemap index automatically.

## Redirects v2

Supported redirect types:

- exact: `old-page` → `/new-page`
- wildcard: `blog/*` → `/articles`
- regex: `#^old/(post-[0-9]+)$#` → `/new/$1`
- gone: `410`

CSV import/export:

```bash
php artisan lazy-seo:redirects-import redirects.csv
php artisan lazy-seo:redirects-export redirects.csv
```

CSV columns:

```csv
old_url,new_url,status_code,enabled,is_regex
old-page,/new-page,301,1,0
#^old/(post-[0-9]+)$#,/new/$1,301,1,1
```

