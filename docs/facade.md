# Facade and helpers

## Facades

```php
use Step2dev\LazySeoTools\Facades\LazySeo;
use Step2dev\LazySeoTools\Facades\Seo;
```

Both facades resolve the package SEO manager:

```php
Seo::title('About us')
    ->description('Learn more about our company.')
    ->canonical(route('about'))
    ->robots(['index', 'follow']);

return Seo::renderMetaTags();
```

## Helpers

```php
seo();
seo_meta();
seo_schema('article', []);
seo_jsonld('article', []);
```

For most Blade layouts, prefer:

```blade
{!! seo_meta() !!}
```

## Resolve without rendering

```php
$data = Seo::resolve(model: $post, url: request()->path(), overrides: [
    'title' => $post->title,
]);

$data->title;
$data->description;
$data->robotsContent();
```

## Resolver priority

`SeoManager::resolve()` merges data in this order:

1. config defaults;
2. URL SEO;
3. model SEO;
4. fluent API/template values;
5. manual overrides.

Example:

```php
$data = seo()
    ->title('Manual title')
    ->resolve(model: $post, url: '/blog/example', overrides: [
        'description' => 'Custom description',
    ]);
```
