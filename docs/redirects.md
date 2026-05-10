# Redirects

Redirect implementation is extracted to [`step2dev/lazy-seo-redirects`](https://github.com/step2dev/lazy-seo-redirects).

`lazy-seo-tools` keeps backward-compatible wrappers only:

- `Step2dev\LazySeoTools\Models\SeoRedirect`
- `Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects`
- `Step2dev\LazySeoTools\Services\RedirectImportExportService`
- old command aliases: `lazy-seo:redirects-import`, `lazy-seo:redirects-export`

For new code, use the extracted package namespace.

## Register middleware

```php
use Step2dev\LazySeoRedirect\Http\Middleware\HandleSeoRedirects;

->withMiddleware(function ($middleware) {
    $middleware->web(append: [
        HandleSeoRedirects::class,
    ]);
})
```

## Create redirects

```php
use Step2dev\LazySeoRedirect\Models\SeoRedirect;

SeoRedirect::query()->create([
    'old_url' => '/old-page',
    'new_url' => '/new-page',
    'status_code' => 301,
    'enabled' => true,
]);
```

Regex redirect:

```php
SeoRedirect::query()->create([
    'old_url' => '#^old/(.*)$#',
    'new_url' => '/new/$1',
    'status_code' => 307,
    'is_regex' => true,
]);
```

## CSV import/export

Preferred commands from the extracted package:

```bash
php artisan lazy-seo-redirects:import redirects.csv
php artisan lazy-seo-redirects:import redirects.csv --no-update
php artisan lazy-seo-redirects:export redirects.csv
```

Legacy aliases kept by `lazy-seo-tools`:

```bash
php artisan lazy-seo:redirects-import redirects.csv
php artisan lazy-seo:redirects-export redirects.csv
```

CSV format:

```csv
old_url,new_url,status_code,enabled,is_regex
old-page,/new-page,301,1,0
#^old/(post-[0-9]+)$#,/new/$1,301,1,1
removed-page,,410,1,0
```
