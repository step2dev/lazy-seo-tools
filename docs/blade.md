## Blade компоненти

## Render all tags

Use the helper in your main layout:

```blade
{!! seo_meta() !!}
```

Or call the manager directly:

```blade
{!! seo()->renderMetaTags() !!}
```

## Components

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
<x-seo::json-ld type="article" :data="$schema" />
<x-seo::schema type="article" :data="$schema" />
```

## Override data in a view

```blade
{!! seo_meta(overrides: [
    'title' => 'Contact',
    'description' => 'Contact our team.',
    'canonical_url' => route('contact'),
]) !!}
```
