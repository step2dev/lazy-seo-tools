# Blade components

## Render all tags

Use the helper in the main layout:

```blade
{!! seo_meta() !!}
```

Or call the manager directly:

```blade
{!! seo()->renderMetaTags() !!}
```

Render once with overrides:

```blade
{!! seo_meta(overrides: [
    'title' => 'Contact',
    'description' => 'Contact our team.',
    'canonical_url' => route('contact'),
]) !!}
```

## Components

The package registers these Blade component aliases when the relevant feature flags are enabled:

```blade
<x-lazy-seo-title title="Home Page" />
<x-lazy-seo-meta />
<x-lazy-seo-og />
<x-lazy-seo-twitter />
<x-lazy-seo-jsonld type="article" :data="$schema" />
```

JSON-LD aliases:

```blade
<x-lazy-seo-schema type="article" :data="$schema" />
<x-lazy-seo::json-ld type="article" :data="$schema" />
<x-lazy-seo::schema type="article" :data="$schema" />
```

Package views are loaded under the `lazy-seo` namespace. Publish them only when the application needs to customize package Blade output:

```bash
php artisan vendor:publish --tag="lazy-seo-views"
```

Published views are placed in `resources/views/vendor/lazy-seo`.

## JSON-LD helpers

Build schema as an array:

```php
$schema = seo_schema('article', [
    'title' => $post->title,
    'description' => $post->excerpt,
    'image' => $post->cover_url,
    'url' => route('posts.show', $post),
]);
```

Render a script tag:

```php
echo seo_jsonld('article', [
    'title' => $post->title,
]);
```

Common schema types include `Article`, `BlogPosting`, `Product`, `Organization`, `LocalBusiness`, `WebSite`, `BreadcrumbList`, `FAQPage` and `WebPage`.


## Tailwind 4

Lazy SEO package views use Tailwind utility classes and do not inject CDN assets. Register the package Blade views from your app CSS entry file:

```css
@import "tailwindcss";
@source "../../vendor/step2dev/lazy-seo-tools/resources/views";
```

Adjust the relative path based on where your compiled CSS entry file lives.
