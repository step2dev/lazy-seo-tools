<?php

return [
    'features' => [
        'meta' => true,
        'schema' => true,
        'redirects' => true,
        'sitemap' => true,
        'crawler' => false,
        'monitoring' => false,
        'indexnow' => false,
        'content_intelligence' => false,
        'og_image' => false,
        'livewire' => false,
        'admin' => false,
        'api' => false,
    ],

    'tables' => [
        'seo' => 'seo',
        'seo_redirects' => 'seo_redirects',
        'seo_templates' => 'seo_templates',
        'seo_scans' => 'seo_scans',
        'seo_scan_issues' => 'seo_scan_issues',
        'seo_indexing_logs' => 'seo_indexing_logs',
    ],

    'defaults' => [
        'title' => 'Laravel',
        'description' => '',
        'keywords' => '',
        'canonical_url' => null,
        'robots' => ['index', 'follow'],
        'image' => null,
        'type' => 'website',
    ],

    'routes' => [
        'web' => false,
        'admin_prefix' => 'lazy-seo',
        'admin_middleware' => ['web', 'auth', 'can:manage-lazy-seo'],
        'admin_gate' => 'manage-lazy-seo',
        'admin_gate_enabled' => true,
        'api' => false,
        'api_prefix' => 'seo',
        'api_middleware' => ['api'],
        'api_read_middleware' => ['auth:sanctum'],
        'api_write_middleware' => ['auth:sanctum'],
        'api_allow_morph_binding' => false,
        'api_allowed_seoable_types' => [],
    ],

    'redirects' => [
        'enabled' => true,
        'preserve_query' => true,
        'regex_enabled' => false,
        'wildcard_enabled' => true,
        'cache_seconds' => 60,
        'allowed_status_codes' => [301, 302, 307, 308, 410],
    ],

    'sitemap' => [
        'path' => 'sitemap.xml',
        'index_path' => 'sitemap.xml',
        'cache_key' => 'lazy-seo.sitemap',
        'cache_store' => null,
        'cache_minutes' => 60,
        'cache_tags_enabled' => false,
        'cache_tags' => ['lazy-seo', 'sitemap'],
        'default_change_frequency' => 'weekly',
        'default_priority' => 0.8,
        'chunk_size' => 50000,
        'max_urls' => 0,
        'gzip' => false,
        'force_index' => false,
        'exclude' => [
            'admin/*',
            'nova/*',
            'horizon/*',
            'telescope/*',
        ],
        'static_urls' => [],
        'models' => [],
    ],

    'cache' => [
        'resolved_minutes' => 0,
    ],

    'templates' => [
        'enabled' => true,
        'default' => null,
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

    'schema' => [
        'organization' => [
            'logo' => null,
            'same_as' => [],
        ],
        'website' => [
            'search_url' => null,
        ],
    ],

    'queue' => [
        'enabled' => true,
        'connection' => null,
        'queue' => 'default',
        'chunk_pages' => 25,
        'tries' => 2,
        'timeout' => 600,
    ],

    'history' => [
        'enabled' => true,
        'trend_limit' => 10,
        'store_regressions' => true,
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
        'enabled' => true,
        'url' => null,
        'schedule' => null,
        'scheduled_queue' => false,
        'max_pages' => 50,
        'fail_under' => 75,
        'pass_score' => 75,
        'keep_scans' => 100,
    ],

    'crawler' => [
        'enabled' => true,
        'max_pages' => 50,
        'max_depth' => 5,
        'timeout' => 10,
        'retry_times' => 1,
        'retry_sleep' => 250,
        'rate_limit_ms' => 250,
        'queue_only' => false,
        'user_agent' => 'LazySeoBot/1.0',
        'respect_noindex' => false,
        'respect_robots_txt' => true,
        'check_external_links' => false,
        'max_external_links' => 50,
        'max_redirects' => 5,
        'max_body_kb' => 1024,
        'allowed_content_types' => ['text/html', 'application/xhtml+xml'],
        'allow_private_networks' => false,
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
        'enabled' => false,
        'key' => null,
        'key_location' => null,
        'endpoint' => 'https://api.indexnow.org/indexnow',
        'host' => null,
        'timeout' => 10,
        'retry_times' => 2,
        'retry_sleep' => 250,
        'chunk_size' => 1000,
        'log' => true,
    ],

    'alerts' => [
        'enabled' => false,
        'score_threshold' => 75,
        'critical_issues_threshold' => 1,
        'new_issues_threshold' => 1,
        'failed_scans' => true,
        'cooldown_minutes' => 60,
        'cache_store' => null,
        'webhook_url' => null,
        'include_issues_limit' => 10,
    ],

    'content_intelligence' => [
        'enabled' => true,
        'min_words' => 300,
        'max_keyword_density' => 3.5,
        'min_keyword_density' => 0.3,
        'max_readability_sentence_words' => 22,
        'internal_link_minimum' => 1,
    ],

    'ai' => [
        'enabled' => false,
        'provider' => 'openai',
        'token' => null,
        'model' => 'gpt-4o-mini',
        'timeout' => 15,
        'retry_times' => 1,
        'retry_sleep' => 250,
    ],

    'ai_token' => null,


    'validation' => [
        'enabled' => true,
    ],
];
