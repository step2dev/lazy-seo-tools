# Quick start

The shortest setup is to render meta tags in your application layout and set SEO data from controllers, Livewire components or route closures.

## Render tags

Add this to your `<head>`:

```blade
{!! seo_meta() !!}
```

The longer equivalent is:

```blade
{!! seo()->renderMetaTags() !!}
```

## Set page SEO

```php
seo()
    ->title('Pricing')
    ->description('Simple pricing for your product.')
    ->canonical(route('pricing'))
    ->image(asset('storage/og/pricing.jpg'))
    ->robots(['index', 'follow']);
```

## Use presets

```php
seo()->preset('article', $post);
seo()->preset('product', $product);
seo()->preset('homepage');
```

You can still override anything at runtime:

```php
seo()
    ->for($post)
    ->with([
        'title' => $post->title,
        'description' => $post->excerpt,
    ]);
```

## Render one-off tags

Use overrides when you do not need to keep fluent state:

```blade
{!! seo_meta(overrides: [
    'title' => 'About us',
    'description' => 'Learn more about our company.',
    'canonical_url' => route('about'),
]) !!}
```

## Model SEO

Add the trait to a model:

```php
use Step2dev\LazySeoTools\Concerns\HasSeo;

class Post extends Model
{
    use HasSeo;
}
```

Save SEO data:

```php
$post->updateSeo([
    'title' => ['en' => $post->title],
    'description' => ['en' => $post->excerpt],
    'canonical_url' => route('posts.show', $post),
    'robots' => ['index', 'follow'],
    'indexable' => true,
]);
```

Resolve it:

```php
$data = seo()->resolve(model: $post, url: request()->path());
```

## Keep it light

Disable modules your application does not use:

```php
'features' => [
    'monitoring' => false,
    'indexnow' => false,
    'livewire' => false,
],
```

## Common commands

```bash
php artisan lazy-seo:sitemap
php artisan lazy-seo:monitor https://example.com
php artisan lazy-seo:crawl https://example.com --max-pages=100
php artisan lazy-seo:indexnow https://example.com/page
```
