# Installation

## Requirements

- PHP `^8.2`
- Laravel `^11.0`, `^12.0` or `^13.0`
- Livewire `^3.6` or `^4.0` when Livewire components are used
- `spatie/laravel-sitemap`
- `spatie/laravel-translatable`
- `intervention/image`

## Install the package

```bash
composer require step2dev/lazy-seo-tools
```

Laravel discovers the service provider automatically:

```php
Step2dev\LazySeoTools\LazySeoServiceProvider::class
```

## Publish config and migrations

```bash
php artisan vendor:publish --tag="lazy-seo-config"
php artisan vendor:publish --tag="lazy-seo-migrations"
php artisan migrate
```

Publish views only when the application needs to customize package Blade output:

```bash
php artisan vendor:publish --tag="lazy-seo-views"
```

## Layout setup

Add the meta renderer to the main layout:

```blade
<head>
    {!! seo_meta() !!}
</head>
```

`seo_meta()` is a helper around `seo()->renderMetaTags()`.

## Configuration

The published file is `config/lazy-seo.php`. It is intentionally compact; advanced defaults from `config/lazy-seo-defaults.php` are merged internally.

Table names are configured directly in the config file and should be changed before migrations run:

```php
'tables' => [
    'seo' => 'seo',
    'seo_redirects' => 'seo_redirects',
    'seo_templates' => 'seo_templates',
    'seo_scans' => 'seo_scans',
    'seo_scan_issues' => 'seo_scan_issues',
    'seo_indexing_logs' => 'seo_indexing_logs',
],
```

Runtime settings may use environment variables. Common examples:

```env
LAZY_SEO_WEB_ROUTES=false
LAZY_SEO_API_ROUTES=false
LAZY_SEO_MONITORING_URL=https://example.com
LAZY_SEO_INDEXNOW_KEY=your-indexnow-key
```

## Feature flags

Disable modules that are not needed by the application:

```php
'features' => [
    'meta' => true,
    'schema' => true,
    'redirects' => true,
    'sitemap' => true,
    'crawler' => true,
    'monitoring' => true,
    'indexnow' => false,
    'content_intelligence' => true,
    'og_image' => true,
    'livewire' => false,
    'admin' => false,
    'api' => false,
],
```
