<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database tables
    |--------------------------------------------------------------------------
    |
    | Change these values directly in the published config file. Do not use env()
    | here: table names must be stable after config:cache.
    |
    */
    'tables' => [
        'seo' => 'seo',
        'seo_redirects' => 'seo_redirects',
        'seo_templates' => 'seo_templates',
    ],

    'defaults' => [
        'title' => config('app.name'),
        'description' => '',
        'keywords' => '',
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
        'enabled' => true,
        'preserve_query' => true,
        'allowed_status_codes' => [301, 302, 307, 308, 410],
    ],

    'sitemap' => [
        'path' => 'sitemap.xml',
        'cache_key' => 'lazy-seo.sitemap',
        'cache_minutes' => 60,
        'default_change_frequency' => 'weekly',
        'default_priority' => 0.8,
    ],

    'og_image' => [
        'disk' => 'public',
        'directory' => 'og',
        'width' => 1200,
        'height' => 630,
    ],

    'webhooks' => [
        'seo.created' => null,
        'seo.updated' => null,
        'seo.deleted' => null,
    ],

    'ai_token' => env('OPENAI_API_KEY'),
];
