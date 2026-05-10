# Installation

## Requirements

- PHP `^8.2`
- Laravel `^11.0`, `^12.0` or `^13.0`
- `spatie/laravel-translatable`

Optional:

- Livewire `^3.6` or `^4.0` when Livewire/admin components are used.
- `intervention/image` when OpenGraph image generation is enabled.
- `spatie/laravel-sitemap` only if your application also wants Spatie sitemap tooling.

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

The package ships as a small core. Advanced modules are opt-in:

```php
'features' => [
    'meta' => true,
    'schema' => true,
    'redirects' => true,
    'sitemap' => true,
    'crawler' => false,
    'monitoring' => false,
    'indexnow' => false,
    'content_intelligence' => false,
    'og_image' => false,
    'livewire' => false,
    'admin' => false,
    'api' => false,
],
```


## Enable advanced layers

Crawler/monitoring:

```php
'features' => [
    'crawler' => true,
    'monitoring' => true,
],
```

Livewire admin UI:

```bash
composer require livewire/livewire
```

```php
'features' => [
    'livewire' => true,
    'admin' => true,
],

'routes' => [
    'web' => true,
],
```

API routes stay disabled until both the feature and routes are enabled:

```php
'features' => [
    'api' => true,
],

'routes' => [
    'api' => true,
],
```


## Optional admin UI styling

The optional Livewire/admin views are Tailwind-first. Add the package views to your app Tailwind config:

```js
content: [
    './resources/**/*.blade.php',
    './vendor/step2dev/lazy-seo-tools/resources/views/**/*.blade.php',
],
```

For local preview only:

```env
LAZY_SEO_TAILWIND_CDN=true
```
