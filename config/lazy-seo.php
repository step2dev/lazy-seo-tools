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
        'admin_middleware' => ['web', 'auth', 'can:manage-lazy-seo'],
        'admin_gate' => env('LAZY_SEO_ADMIN_GATE', 'manage-lazy-seo'),
        'admin_gate_enabled' => env('LAZY_SEO_ADMIN_GATE_ENABLED', true),
        'api' => env('LAZY_SEO_API_ROUTES', false),
        'api_prefix' => env('LAZY_SEO_API_PREFIX', 'seo'),
        'api_middleware' => ['api'],
        'api_read_middleware' => ['auth:sanctum'],
        'api_write_middleware' => ['auth:sanctum'],
        'api_allow_morph_binding' => env('LAZY_SEO_API_ALLOW_MORPH_BINDING', false),
        'api_allowed_seoable_types' => [
            // App\Models\Post::class,
        ],
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
    | Redirects
    |--------------------------------------------------------------------------
    */
    'redirects' => [
        'enabled' => env('LAZY_SEO_REDIRECTS_ENABLED', true),
        'preserve_query' => env('LAZY_SEO_REDIRECT_PRESERVE_QUERY', true),
        'regex_enabled' => env('LAZY_SEO_REDIRECT_REGEX_ENABLED', false),
        'wildcard_enabled' => env('LAZY_SEO_REDIRECT_WILDCARD_ENABLED', true),
        'cache_seconds' => (int) env('LAZY_SEO_REDIRECT_CACHE_SECONDS', 60),
        'allowed_status_codes' => [301, 302, 307, 308, 410],
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

    /*
    |--------------------------------------------------------------------------
    | Crawler
    |--------------------------------------------------------------------------
    */
    'crawler' => [
        'max_pages' => (int) env('LAZY_SEO_CRAWLER_MAX_PAGES', 50),
        'max_depth' => (int) env('LAZY_SEO_CRAWLER_MAX_DEPTH', 5),
        'timeout' => (int) env('LAZY_SEO_CRAWLER_TIMEOUT', 10),
        'retry_times' => (int) env('LAZY_SEO_CRAWLER_RETRY_TIMES', 1),
        'retry_sleep' => (int) env('LAZY_SEO_CRAWLER_RETRY_SLEEP', 250),
        'rate_limit_ms' => (int) env('LAZY_SEO_CRAWLER_RATE_LIMIT_MS', 250),
        'queue_only' => (bool) env('LAZY_SEO_CRAWLER_QUEUE_ONLY', false),
        'respect_noindex' => (bool) env('LAZY_SEO_CRAWLER_RESPECT_NOINDEX', false),
        'respect_robots_txt' => (bool) env('LAZY_SEO_CRAWLER_RESPECT_ROBOTS_TXT', true),
        'check_external_links' => (bool) env('LAZY_SEO_CRAWLER_CHECK_EXTERNAL_LINKS', false),
        'max_external_links' => (int) env('LAZY_SEO_CRAWLER_MAX_EXTERNAL_LINKS', 50),
        'max_redirects' => (int) env('LAZY_SEO_CRAWLER_MAX_REDIRECTS', 5),
        'max_body_kb' => (int) env('LAZY_SEO_CRAWLER_MAX_BODY_KB', 1024),
        'allowed_content_types' => ['text/html', 'application/xhtml+xml'],
        'allow_private_networks' => (bool) env('LAZY_SEO_CRAWLER_ALLOW_PRIVATE_NETWORKS', false),
        'allowed_hosts' => [],
        'blocked_hosts' => [],
        'exclude' => [
            'admin/*',
            'nova/*',
            'horizon/*',
            'telescope/*',
            'login',
            'logout',
        ],
    ],

    'indexnow' => [
        'key' => env('LAZY_SEO_INDEXNOW_KEY'),
        'host' => env('LAZY_SEO_INDEXNOW_HOST'),
    ],

    'monitoring' => [
        'url' => env('LAZY_SEO_MONITORING_URL', env('APP_URL')),
        'schedule' => env('LAZY_SEO_MONITORING_SCHEDULE'),
    ],

    'ai' => [
        'enabled' => env('LAZY_SEO_AI_ENABLED', false),
        'provider' => env('LAZY_SEO_AI_PROVIDER', 'openai'),
        'token' => env('LAZY_SEO_AI_TOKEN', env('OPENAI_API_KEY')),
        'model' => env('LAZY_SEO_AI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('LAZY_SEO_AI_TIMEOUT', 15),
        'retry_times' => (int) env('LAZY_SEO_AI_RETRY_TIMES', 1),
        'retry_sleep' => (int) env('LAZY_SEO_AI_RETRY_SLEEP', 250),
    ],

    'alerts' => [
        'enabled' => env('LAZY_SEO_ALERTS_ENABLED', false),
        'webhook_url' => env('LAZY_SEO_ALERT_WEBHOOK_URL'),
    ],

    'validation' => [
        'enabled' => env('LAZY_SEO_CONFIG_VALIDATION', true),
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
