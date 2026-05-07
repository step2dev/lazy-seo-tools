## Встановлення

```bash
composer require step2dev/lazy-seo-tools
```

Publish the config:

```bash
php artisan vendor:publish --tag="lazy-seo-config"
```

Publish migrations and migrate:

```bash
php artisan vendor:publish --tag="lazy-seo-migrations"
php artisan migrate
```

Views are optional. Publish them only when you want to customize the Blade output:

```bash
php artisan vendor:publish --tag="lazy-seo-views"
```

The service provider is auto-discovered by Laravel:

```php
Step2dev\LazySeoTools\LazySeoServiceProvider::class
```

## Layout setup

Add one line to your main layout:

```blade
<head>
    {!! seo_meta() !!}
</head>
```
