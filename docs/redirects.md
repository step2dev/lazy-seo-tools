# Redirects

The package includes `HandleSeoRedirects` middleware for exact, wildcard and regex redirects.

## Register middleware

Laravel 11+ style:

```php
use Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects;

->withMiddleware(function ($middleware) {
    $middleware->web(append: [
        HandleSeoRedirects::class,
    ]);
})
```

Classic `app/Http/Kernel.php` style:

```php
protected $middlewareGroups = [
    'web' => [
        \Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects::class,
    ],
];
```

## Create redirects

```php
use Step2dev\LazySeoTools\Models\SeoRedirect;

SeoRedirect::query()->create([
    'old_url' => '/old-page',
    'new_url' => '/new-page',
    'status_code' => 301,
    'enabled' => true,
]);
```

Supported status codes are `301`, `302`, `307`, `308` and `410`.

## Redirect types

Exact redirect:

```php
'old_url' => '/old-page'
```

Wildcard redirect:

```php
'old_url' => '/blog/*'
```

Regex redirect:

```php
'old_url' => '#^old/(post-[0-9]+)$#',
'is_regex' => true,
'new_url' => '/new/$1',
```

`410` redirects return a Gone response and do not need a target URL.

## CSV import/export

Import redirects:

```bash
php artisan lazy-seo:redirects-import redirects.csv
```

Import without updating existing rows:

```bash
php artisan lazy-seo:redirects-import redirects.csv --no-update
```

Export redirects:

```bash
php artisan lazy-seo:redirects-export redirects.csv
```

CSV format:

```csv
old_url,new_url,status_code,enabled,is_regex
old-page,/new-page,301,1,0
#^old/(post-[0-9]+)$#,/new/$1,301,1,1
removed-page,,410,1,0
```
