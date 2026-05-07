<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database tables
    |--------------------------------------------------------------------------
    |
    | Change these values directly in the published config file. Do not use env()
    | here: table names must be stable after config:cache and migrations must stay
    | deterministic across deploys.
    |
    */
    'tables' => [
        'seo' => 'seo',
        'seo_redirects' => 'seo_redirects',
        'seo_templates' => 'seo_templates',
        'seo_scans' => 'seo_scans',
        'seo_scan_issues' => 'seo_scan_issues',
    ],

    'defaults' => [
        'title' => env('APP_NAME', 'Laravel'),
        'description' => '',
        'keywords' => '',
        'canonical_url' => null,
        'robots' => ['index', 'follow'],
        'image' => null,
        'type' => 'website',
    ],

    'routes' => [
        'web' => env('LAZY_SEO_WEB_ROUTES', false),
        'admin_prefix' => env('LAZY_SEO_ADMIN_PREFIX', 'lazy-seo'),
        'admin_middleware' => ['web'],
        'api' => env('LAZY_SEO_API_ROUTES', false),
        'api_prefix' => env('LAZY_SEO_API_PREFIX', 'seo'),
    ],

    'redirects' => [
        'enabled' => env('LAZY_SEO_REDIRECTS_ENABLED', true),
        'preserve_query' => env('LAZY_SEO_REDIRECTS_PRESERVE_QUERY', true),
        'regex_enabled' => env('LAZY_SEO_REDIRECTS_REGEX_ENABLED', true),
        'wildcard_enabled' => env('LAZY_SEO_REDIRECTS_WILDCARD_ENABLED', true),
        'allowed_status_codes' => [301, 302, 307, 308, 410],
    ],

    'sitemap' => [
        'path' => env('LAZY_SEO_SITEMAP_PATH', 'sitemap.xml'),
        'index_path' => env('LAZY_SEO_SITEMAP_INDEX_PATH', 'sitemap.xml'),
        'cache_key' => env('LAZY_SEO_SITEMAP_CACHE_KEY', 'lazy-seo.sitemap'),
        'cache_minutes' => env('LAZY_SEO_SITEMAP_CACHE_MINUTES', 60),
        'default_change_frequency' => env('LAZY_SEO_SITEMAP_CHANGE_FREQUENCY', 'weekly'),
        'default_priority' => (float) env('LAZY_SEO_SITEMAP_PRIORITY', 0.8),
        'chunk_size' => (int) env('LAZY_SEO_SITEMAP_CHUNK_SIZE', 50000),
        'gzip' => env('LAZY_SEO_SITEMAP_GZIP', false),
        'force_index' => env('LAZY_SEO_SITEMAP_FORCE_INDEX', false),
        'exclude' => [
            'admin/*',
            'nova/*',
            'horizon/*',
            'telescope/*',
        ],
        'static_urls' => [
            // ['loc' => '/', 'changefreq' => 'daily', 'priority' => 1.0],
        ],
        'models' => [
            // App\Models\Post::class => [
            //     'enabled' => true,
            //     'url' => 'getSeoUrl',
            //     'scope' => 'published',
            //     'lastmod_column' => 'updated_at',
            //     'changefreq' => 'weekly',
            //     'priority' => 0.8,
            // ],
        ],
    ],

    'cache' => [
        'resolved_minutes' => env('LAZY_SEO_RESOLVED_CACHE_MINUTES', 0),
    ],

    'templates' => [
        'enabled' => env('LAZY_SEO_TEMPLATES_ENABLED', true),
        'default' => env('LAZY_SEO_DEFAULT_TEMPLATE', null),
    ],

    'og_image' => [
        'disk' => env('LAZY_SEO_OG_DISK', 'public'),
        'directory' => env('LAZY_SEO_OG_DIRECTORY', 'og'),
        'width' => env('LAZY_SEO_OG_WIDTH', 1200),
        'height' => env('LAZY_SEO_OG_HEIGHT', 630),
    ],

    'webhooks' => [
        'seo.created' => env('LAZY_SEO_WEBHOOK_CREATED'),
        'seo.updated' => env('LAZY_SEO_WEBHOOK_UPDATED'),
        'seo.deleted' => env('LAZY_SEO_WEBHOOK_DELETED'),
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

    'monitoring' => [
        'enabled' => env('LAZY_SEO_MONITORING_ENABLED', true),
        'url' => env('LAZY_SEO_MONITORING_URL', env('APP_URL')),
        'schedule' => env('LAZY_SEO_MONITORING_SCHEDULE', null),
        'max_pages' => (int) env('LAZY_SEO_MONITORING_MAX_PAGES', 50),
        'fail_under' => (int) env('LAZY_SEO_MONITORING_FAIL_UNDER', 75),
        'pass_score' => 75,
        'keep_scans' => (int) env('LAZY_SEO_MONITORING_KEEP_SCANS', 100),
    ],

    'crawler' => [
        'enabled' => env('LAZY_SEO_CRAWLER_ENABLED', true),
        'max_pages' => (int) env('LAZY_SEO_CRAWLER_MAX_PAGES', 50),
        'timeout' => (int) env('LAZY_SEO_CRAWLER_TIMEOUT', 10),
        'user_agent' => env('LAZY_SEO_CRAWLER_USER_AGENT', 'LazySeoBot/1.0'),
        'respect_noindex' => env('LAZY_SEO_CRAWLER_RESPECT_NOINDEX', false),
        'exclude' => [
            'admin/*',
            'nova/*',
            'horizon/*',
            'telescope/*',
            'login',
            'logout',
        ],
    ],

    'ai_token' => env('LAZY_SEO_AI_TOKEN'),
];
