## Redirects

Register the middleware in your web stack:

```php
use Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects;
```

Laravel 11+ style:

```php
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

Create a redirect:

```php
use Step2dev\LazySeoTools\Models\SeoRedirect;

SeoRedirect::query()->create([
    'old_url' => '/old-page',
    'new_url' => '/new-page',
    'status_code' => 301,
    'enabled' => true,
]);
```

Import and export CSV files:

```bash
php artisan lazy-seo:redirects-import redirects.csv
php artisan lazy-seo:redirects-export redirects.csv
```
