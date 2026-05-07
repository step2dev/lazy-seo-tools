## Facade

```php
use Step2dev\LazySeoTools\Facades\Seo;

Seo::title('About us')
    ->description('Learn more about our company.')
    ->canonical(route('about'));

return Seo::renderMetaTags();
```

Resolve data without rendering:

```php
$data = Seo::resolve(model: $post, url: request()->path(), overrides: [
    'title' => $post->title,
]);

$data->title;
$data->description;
```

For most Blade layouts, prefer the helper:

```blade
{!! seo_meta() !!}
```
