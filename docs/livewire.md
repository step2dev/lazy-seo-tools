# Livewire components

Livewire components are registered when Livewire is installed and the `livewire` feature flag is enabled.

```php
'features' => [
    'livewire' => true,
],
```

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

Web admin routes are disabled by default. Enable them in `config/lazy-seo.php`:

```php
'features' => [
    'admin' => true,
    'livewire' => true,
],

'routes' => [
    'web' => true,
    'admin_prefix' => 'lazy-seo',
    'admin_middleware' => ['web', 'auth'],
],
```

Available pages:

```text
/lazy-seo/dashboard
/lazy-seo/issues
/lazy-seo/scans/{scan}
/lazy-seo/redirects
```

Named routes:

```text
lazy-seo.dashboard
lazy-seo.issues
lazy-seo.scans.show
lazy-seo.redirects
```
