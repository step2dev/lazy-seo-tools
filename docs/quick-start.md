# Quick start

The shortest setup is to render meta tags in the application layout and set SEO data from controllers, route closures, Livewire components or view composers.

## Render tags

Add this to the layout `<head>`:

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
    ->type('website')
    ->robots(['index', 'follow']);
```

## Use presets

```php
seo()->preset('article', $post);
seo()->preset('product', $product);
seo()->preset('homepage');
```

Override preset data at runtime:

```php
seo()
    ->for($post)
    ->with([
        'title' => $post->title,
        'description' => $post->excerpt,
    ]);
```

## Render one-off tags

Use overrides when the view does not need fluent state:

```blade
{!! seo_meta(overrides: [
    'title' => 'About us',
    'description' => 'Learn more about our company.',
    'canonical_url' => route('about'),
]) !!}
```

## Model SEO

Add the trait to an Eloquent model:

```php
use Illuminate\Database\Eloquent\Model;
use Step2dev\LazySeoTools\Concerns\HasSeo;

class Post extends Model
{
    use HasSeo;
}
```

Save SEO data:

```php
$post->updateSeo([
    'title' => ['en' => $post->title, 'uk' => $post->title_uk],
    'description' => ['en' => $post->excerpt],
    'keywords' => ['en' => 'laravel, seo'],
    'canonical_url' => route('posts.show', $post),
    'robots' => ['index', 'follow'],
    'indexable' => true,
]);
```

Resolve data:

```php
$data = seo()->resolve(model: $post, url: request()->path());

$data->title;
$data->description;
$data->robotsContent();
```

## URL SEO

```php
use Step2dev\LazySeoTools\Models\Seo;

Seo::query()->create([
    'url' => '/pricing',
    'title' => ['en' => 'Pricing'],
    'description' => ['en' => 'Simple pricing for your product'],
    'robots' => ['index', 'follow'],
    'indexable' => true,
]);
```

```php
$seo = seo()->forUrl('/pricing');
$data = seo()->resolve(url: '/pricing');
```

## Templates

SEO templates are stored in `seo_templates` and support `{key}` placeholders:

```php
use Step2dev\LazySeoTools\Models\SeoTemplate;

SeoTemplate::query()->create([
    'name' => 'post',
    'title' => ['en' => '{title} | {site_name}'],
    'description' => ['en' => '{excerpt}'],
    'payload' => ['type' => 'article'],
    'enabled' => true,
]);
```

```php
seo()->template('post', [
    'title' => $post->title,
    'excerpt' => $post->excerpt,
    'site_name' => config('app.name'),
]);
```

## Core commands

```bash
php artisan lazy-seo:about
php artisan lazy-seo:sitemap
php artisan lazy-seo:sitemap:warm
```

Advanced commands are available after enabling their feature flags:

```bash
php artisan lazy-seo:crawl https://example.com --max-pages=100
php artisan lazy-seo:monitor https://example.com
php artisan lazy-seo:history
php artisan lazy-seo:indexnow https://example.com/page
php artisan lazy-seo:content storage/app/page.html
```
