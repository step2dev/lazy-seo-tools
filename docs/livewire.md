# Livewire components

Livewire is optional. The package does not require it for core SEO metadata, schema, redirects or sitemap generation.

Install Livewire only when you want the package UI/admin layer:

```bash
composer require livewire/livewire
```

Enable the feature flags:

```php
'features' => [
    'livewire' => true,
    'admin' => true,
],
```

If admin web routes are enabled without Livewire, Lazy SEO will fail fast with a clear config validation error instead of silently rendering broken pages.

## Components

```blade
<livewire:lazy-seo-form :model="$page" />
<livewire:lazy-seo-analyzer />
<livewire:lazy-seo-redirect-table />
<livewire:lazy-seo-monitoring-dashboard />
<livewire:lazy-seo-issues-table />
<livewire:lazy-seo-scan-detail :scan="$scan" />
```

## Admin routes

Web admin routes are disabled by default. Enable them explicitly:

```php
'features' => [
    'admin' => true,
    'livewire' => true,
],

'routes' => [
    'web' => true,
    'admin_prefix' => 'lazy-seo',
    'admin_middleware' => ['web', 'auth', 'can:manage-lazy-seo'],
],
```

Available pages:

```text
/lazy-seo/dashboard
/lazy-seo/issues
/lazy-seo/scans/{scan}
/lazy-seo/redirects
```

## Tailwind 4

Package Blade views use Tailwind utility classes and never load Tailwind from CDN. Register the package views in your app CSS entry file with Tailwind 4 `@source`:

```css
@import "tailwindcss";
@source "../../vendor/step2dev/lazy-seo-tools/resources/views";
```

Adjust the relative path based on where your compiled CSS entry file lives.
