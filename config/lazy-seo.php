<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Start with the core meta/schema features, then enable the heavier modules
    | only when the application needs them.
    |
    */
    'features' => [
        'meta' => env('LAZY_SEO_FEATURE_META', true),
        'schema' => env('LAZY_SEO_FEATURE_SCHEMA', true),
        'redirects' => env('LAZY_SEO_FEATURE_REDIRECTS', true),
        'sitemap' => env('LAZY_SEO_FEATURE_SITEMAP', true),
        'crawler' => env('LAZY_SEO_FEATURE_CRAWLER', true),
        'monitoring' => env('LAZY_SEO_FEATURE_MONITORING', true),
        'indexnow' => env('LAZY_SEO_FEATURE_INDEXNOW', true),
        'content_intelligence' => env('LAZY_SEO_FEATURE_CONTENT_INTELLIGENCE', true),
        'og_image' => env('LAZY_SEO_FEATURE_OG_IMAGE', true),
        'livewire' => env('LAZY_SEO_FEATURE_LIVEWIRE', true),
        'admin' => env('LAZY_SEO_FEATURE_ADMIN', true),
        'api' => env('LAZY_SEO_FEATURE_API', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'title' => env('APP_NAME', 'Laravel'),
        'description' => '',
        'keywords' => '',
        'canonical_url' => null,
        'robots' => ['index', 'follow'],
        'image' => null,
        'type' => 'website',
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'web' => env('LAZY_SEO_WEB_ROUTES', false),
        'admin_prefix' => env('LAZY_SEO_ADMIN_PREFIX', 'lazy-seo'),
        'admin_middleware' => ['web'],
        'api' => env('LAZY_SEO_API_ROUTES', false),
        'api_prefix' => env('LAZY_SEO_API_PREFIX', 'seo'),
        'api_middleware' => ['api'],
        'api_write_middleware' => [],
        'api_allow_morph_binding' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database tables
    |--------------------------------------------------------------------------
    |
    | Change these before running migrations if your application needs custom
    | table names. Keep them stable after deploys.
    |
    */
    'tables' => [
        'seo' => 'seo',
        'seo_redirects' => 'seo_redirects',
        'seo_templates' => 'seo_templates',
        'seo_scans' => 'seo_scans',
        'seo_scan_issues' => 'seo_scan_issues',
        'seo_indexing_logs' => 'seo_indexing_logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap
    |--------------------------------------------------------------------------
    */
    'sitemap' => [
        'path' => env('LAZY_SEO_SITEMAP_PATH', 'sitemap.xml'),
        'index_path' => env('LAZY_SEO_SITEMAP_INDEX_PATH', 'sitemap.xml'),
        'static_urls' => [
            // ['loc' => '/', 'changefreq' => 'daily', 'priority' => 1.0],
        ],
        'models' => [
            // App\Models\Post::class => [
            //     'url' => 'getSeoUrl',
            //     'scope' => 'published',
            //     'lastmod_column' => 'updated_at',
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Integrations
    |--------------------------------------------------------------------------
    */
    'indexnow' => [
        'key' => env('LAZY_SEO_INDEXNOW_KEY'),
        'host' => env('LAZY_SEO_INDEXNOW_HOST'),
    ],

    'monitoring' => [
        'url' => env('LAZY_SEO_MONITORING_URL', env('APP_URL')),
        'schedule' => env('LAZY_SEO_MONITORING_SCHEDULE'),
    ],

    'alerts' => [
        'enabled' => env('LAZY_SEO_ALERTS_ENABLED', false),
        'webhook_url' => env('LAZY_SEO_ALERT_WEBHOOK_URL'),
    ],

    'schema' => [
        'organization' => [
            'logo' => env('LAZY_SEO_ORGANIZATION_LOGO'),
            'same_as' => [],
        ],
        'website' => [
            'search_url' => env('LAZY_SEO_SEARCH_URL'),
        ],
    ],
];
