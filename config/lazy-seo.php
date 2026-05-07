<?php

return [
    'defaults' => [
        'title' => env('LAZY_SEO_TITLE', config('app.name')),
        'description' => env('LAZY_SEO_DESCRIPTION', ''),
        'keywords' => env('LAZY_SEO_KEYWORDS', ''),
        'canonical_url' => null,
        'robots' => ['index', 'follow'],
        'image' => null,
        'type' => 'website',
    ],

    'routes' => [
        'web' => env('LAZY_SEO_WEB_ROUTES', false),
        'api' => env('LAZY_SEO_API_ROUTES', false),
        'api_prefix' => env('LAZY_SEO_API_PREFIX', 'seo'),
    ],

    'redirects' => [
        'enabled' => env('LAZY_SEO_REDIRECTS_ENABLED', true),
        'preserve_query' => env('LAZY_SEO_REDIRECTS_PRESERVE_QUERY', true),
    ],

    'sitemap' => [
        'path' => env('LAZY_SEO_SITEMAP_PATH', 'sitemap.xml'),
        'cache_key' => 'lazy-seo.sitemap',
        'cache_minutes' => 60,
    ],

    'og_image' => [
        'disk' => env('LAZY_SEO_OG_DISK', 'public'),
        'directory' => env('LAZY_SEO_OG_DIRECTORY', 'og'),
        'width' => 1200,
        'height' => 630,
    ],

    'webhooks' => [
        'seo.created' => env('SEO_WEBHOOK_CREATED'),
        'seo.updated' => env('SEO_WEBHOOK_UPDATED'),
        'seo.deleted' => env('SEO_WEBHOOK_DELETED'),
    ],

    'ai_token' => env('OPENAI_API_KEY'),
];
