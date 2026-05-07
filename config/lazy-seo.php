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
        'seo_indexing_logs' => 'seo_indexing_logs',
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
        'api_middleware' => ['api'],
        'api_write_middleware' => [],
        'api_allow_morph_binding' => false,
    ],

    'redirects' => [
        'enabled' => env('LAZY_SEO_REDIRECTS_ENABLED', true),
        'preserve_query' => env('LAZY_SEO_REDIRECTS_PRESERVE_QUERY', true),
        'regex_enabled' => env('LAZY_SEO_REDIRECTS_REGEX_ENABLED', true),
        'wildcard_enabled' => env('LAZY_SEO_REDIRECTS_WILDCARD_ENABLED', true),
        'cache_seconds' => (int) env('LAZY_SEO_REDIRECTS_CACHE_SECONDS', 60),
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
            // [
            //     'loc' => '/',
            //     'changefreq' => 'daily',
            //     'priority' => 1.0,
            //     'images' => [
            //         ['loc' => '/images/home-og.jpg', 'title' => 'Home'],
            //     ],
            //     'alternates' => [
            //         'uk' => '/uk',
            //         'en' => '/en',
            //     ],
            // ],
        ],
        'models' => [
            // App\Models\Post::class => [
            //     'enabled' => true,
            //     'url' => 'getSeoUrl',
            //     'scope' => 'published',
            //     'lastmod_column' => 'updated_at',
            //     'changefreq' => 'weekly',
            //     'priority' => 0.8,
            //     'images' => 'getSeoImages',
            //     'alternates' => 'getSeoAlternates',
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

    'queue' => [
        'enabled' => env('LAZY_SEO_QUEUE_ENABLED', true),
        'connection' => env('LAZY_SEO_QUEUE_CONNECTION'),
        'queue' => env('LAZY_SEO_QUEUE_NAME', 'default'),
        'chunk_pages' => (int) env('LAZY_SEO_QUEUE_CHUNK_PAGES', 25),
    ],

    'history' => [
        'enabled' => env('LAZY_SEO_HISTORY_ENABLED', true),
        'trend_limit' => (int) env('LAZY_SEO_HISTORY_TREND_LIMIT', 10),
        'store_regressions' => env('LAZY_SEO_HISTORY_STORE_REGRESSIONS', true),
    ],

    'audit' => [
        'severity_weights' => [
            'error' => 8,
            'warning' => 4,
            'notice' => 1,
        ],
        'max_score_penalty' => 100,
        'checks' => [
            'http_error' => true,
            'missing_title' => true,
            'title_length' => true,
            'duplicate_title' => true,
            'missing_description' => true,
            'description_length' => true,
            'duplicate_description' => true,
            'missing_h1' => true,
            'multiple_h1' => true,
            'missing_canonical' => true,
            'canonical_conflict' => true,
            'noindex' => true,
            'missing_image_alt' => true,
            'broken_link' => true,
            'broken_external_link' => true,
            'redirect_chain' => true,
            'orphan_page' => true,
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
        'check_external_links' => env('LAZY_SEO_CRAWLER_CHECK_EXTERNAL_LINKS', false),
        'max_external_links' => (int) env('LAZY_SEO_CRAWLER_MAX_EXTERNAL_LINKS', 50),
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
        'enabled' => env('LAZY_SEO_INDEXNOW_ENABLED', false),
        'key' => env('LAZY_SEO_INDEXNOW_KEY'),
        'key_location' => env('LAZY_SEO_INDEXNOW_KEY_LOCATION'),
        'endpoint' => env('LAZY_SEO_INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow'),
        'host' => env('LAZY_SEO_INDEXNOW_HOST'),
        'timeout' => (int) env('LAZY_SEO_INDEXNOW_TIMEOUT', 10),
        'retry_times' => (int) env('LAZY_SEO_INDEXNOW_RETRY_TIMES', 2),
        'retry_sleep' => (int) env('LAZY_SEO_INDEXNOW_RETRY_SLEEP', 250),
        'chunk_size' => (int) env('LAZY_SEO_INDEXNOW_CHUNK_SIZE', 1000),
        'log' => env('LAZY_SEO_INDEXNOW_LOG', true),
    ],

    'content_intelligence' => [
        'enabled' => env('LAZY_SEO_CONTENT_INTELLIGENCE_ENABLED', true),
        'min_words' => (int) env('LAZY_SEO_CONTENT_MIN_WORDS', 300),
        'max_keyword_density' => (float) env('LAZY_SEO_CONTENT_MAX_KEYWORD_DENSITY', 3.5),
        'min_keyword_density' => (float) env('LAZY_SEO_CONTENT_MIN_KEYWORD_DENSITY', 0.3),
        'max_readability_sentence_words' => (int) env('LAZY_SEO_CONTENT_MAX_SENTENCE_WORDS', 22),
        'internal_link_minimum' => (int) env('LAZY_SEO_CONTENT_INTERNAL_LINK_MINIMUM', 1),
    ],

    'ai_token' => env('LAZY_SEO_AI_TOKEN'),
];
