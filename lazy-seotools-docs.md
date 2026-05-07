# Lazy SEO Tools

Laravel SEO package built on `spatie/laravel-package-tools`.

## Features

- SEO records for URLs and Eloquent models
- Configurable table names without `env()`
- Translatable title, description and keywords via `spatie/laravel-translatable`
- Meta tags rendering through `Seo` / `LazySeo` facades and `seo()` helper
- `HasSeo` trait for models
- SEO redirects middleware with exact and wildcard redirects
- Redirect hit counter and loop protection
- Sitemap generation via `spatie/laravel-sitemap`
- Blade components
- Optional Livewire components
- Optional API routes disabled by default
- Optional OG image generator via Intervention Image v3

## Installation

```bash
composer require step2dev/lazy-seo-tools
```

Publish config and migrations:

```bash
php artisan vendor:publish --tag="lazy-seo-config"
php artisan vendor:publish --tag="lazy-seo-migrations"
php artisan migrate
```

The package provider is `Step2dev\LazySeoTools\LazySeoServiceProvider` and is registered through Laravel package discovery.

## Configuration

```php
// config/lazy-seo.php
return [
    'tables' => [
        'seo' => 'seo',
        'seo_redirects' => 'seo_redirects',
        'seo_templates' => 'seo_templates',
    ],

    'defaults' => [
        'title' => config('app.name'),
        'description' => '',
        'keywords' => '',
        'robots' => ['index', 'follow'],
    ],

    'routes' => [
        'web' => false,
        'api' => false,
        'api_prefix' => 'seo',
    ],
];
```

Table names are changed directly in the published config. No `env()` is used for table names because migrations and `config:cache` must stay deterministic.

Example:

```php
'tables' => [
    'seo' => 'custom_seo',
    'seo_redirects' => 'custom_seo_redirects',
    'seo_templates' => 'custom_seo_templates',
],
```

API and web routes are disabled by default.

## Model SEO

```php
use Illuminate\Database\Eloquent\Model;
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
    'keywords' => ['en' => 'laravel, seo'],
    'canonical_url' => 'https://example.com/posts/post-title',
]);
```

## URL SEO

```php
use Step2dev\LazySeoTools\Models\Seo;

Seo::create([
    'url' => '/about',
    'title' => ['en' => 'About us'],
    'description' => ['en' => 'About page description'],
    'keywords' => ['en' => 'about, company'],
]);
```

## Rendering meta tags

```blade
{!! Seo::renderMetaTags() !!}
```

For a model:

```blade
{!! Seo::renderMetaTags(Seo::forModel($post)) !!}
```

With overrides:

```blade
{!! Seo::renderMetaTags(overrides: ['image' => asset('og/post.png')]) !!}
```

Fluent helper:

```php
seo()
    ->title('Custom title')
    ->description('Custom description')
    ->image(asset('og.png'));
```

## Blade components

```blade
<x-lazy-seo-meta />
<x-lazy-seo-title title="Custom title" />
<x-lazy-seo-og :overrides="['image' => asset('og.png')]" />
<x-lazy-seo-jsonld :data="['title' => 'Page title']" />
```

The package also registers the `seo` view namespace, so anonymous components work too:

```blade
<x-seo::meta />
```

## Redirect middleware

Register the middleware in your Laravel app.

Laravel 11/12:

```php
// bootstrap/app.php
use Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects;

->withMiddleware(function ($middleware) {
    $middleware->web(append: [
        HandleSeoRedirects::class,
    ]);
})
```

Create redirects:

```php
use Step2dev\LazySeoTools\Models\SeoRedirect;

SeoRedirect::create([
    'old_url' => 'old-page',
    'new_url' => '/new-page',
    'status_code' => 301,
]);

SeoRedirect::create([
    'old_url' => 'docs/*',
    'new_url' => '/documentation',
    'status_code' => 302,
]);
```

`410` redirects return Gone.

## Sitemap

Generate sitemap from configured SEO table records where `url` is present and `indexable = true`:

```bash
php artisan lazy-seo:sitemap
```

Custom path inside `public`:

```bash
php artisan lazy-seo:sitemap --path=sitemaps/main.xml
```

Programmatic usage:

```php
app(\Step2dev\LazySeoTools\Services\SitemapGeneratorService::class)->generate([
    ['loc' => '/about', 'priority' => 0.8, 'freq' => 'weekly'],
]);
```

## Livewire

Registered components:

```blade
<livewire:lazy-seo-form />
<livewire:lazy-seo-analyzer />
<livewire:lazy-seo-redirect-table />
```

## Testing

```bash
composer install
vendor/bin/pest
vendor/bin/pint
```

## Notes

This package intentionally keeps API routes disabled by default. Enable them only when your app really needs headless SEO management.

## Queueable crawler and history

Use `lazy-seo:crawl-queue` for async scans. Use `lazy-seo:history` to inspect score trends, regressions and resolved issues.
