## Livewire компоненти

```blade
<livewire:lazy-seo-form :model="$page" />
<livewire:lazy-seo-analyzer />
<livewire:lazy-seo-redirect-table />
<livewire:lazy-seo-monitoring-dashboard />
<livewire:lazy-seo-issues-table />
<livewire:lazy-seo-scan-detail :scan="$scan" />
```

Web admin routes are disabled by default. Enable them in `config/lazy-seo.php`:

```php
'routes' => [
    'web' => true,
    'admin_prefix' => 'lazy-seo',
    'admin_middleware' => ['web'],
],
```
