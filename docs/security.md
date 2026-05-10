# Security

Lazy SEO Tools ships with safe defaults for public applications.

## Admin routes

Admin routes are disabled by default. When enabled, they require both authentication and an authorization gate:

```php
'web' => env('LAZY_SEO_WEB_ROUTES', false),
'admin_middleware' => ['web', 'auth', 'can:manage-lazy-seo'],
'admin_gate' => 'manage-lazy-seo',
```

The package registers a default `manage-lazy-seo` gate. It allows users with a `manage seo` ability/permission, including common permission packages that expose `can()` or `hasPermissionTo()`.

You can replace the gate name:

```php
'admin_gate' => 'manage seo',
'admin_middleware' => ['web', 'auth', 'can:manage seo'],
```

## API write routes

API routes are disabled by default. Write routes require `auth:sanctum` by default:

```php
'api' => env('LAZY_SEO_API_ROUTES', false),
'api_read_middleware' => [],
'api_write_middleware' => ['auth:sanctum'],
```

Do not enable write routes without authentication. Add `auth:sanctum` or a project-specific guard to `api_read_middleware` if SEO records should not be public.

## Crawler SSRF protection

The crawler blocks private/reserved network targets by default:

```php
'allow_private_networks' => false,
'allowed_hosts' => [],
'blocked_hosts' => [],
'max_redirects' => 5,
'max_body_kb' => 1024,
```

Use `allowed_hosts` for production scans whenever possible.

## AI

AI is disabled by default and requires an explicit token:

```php
'ai' => [
    'enabled' => false,
    'provider' => 'openai',
    'token' => env('LAZY_SEO_AI_TOKEN', env('OPENAI_API_KEY')),
],
```

The AI layer is provider-based, so extra providers can be added without changing the package services.

## Config validation

Runtime config validation is enabled by default:

```php
'validation' => [
    'enabled' => true,
],
```

It fails fast when admin/API/crawler/AI config is unsafe. Disable only when you intentionally accept the risk.

## Production crawler limits

For production, prefer queued scans for anything larger than a tiny site:

```php
'crawler' => [
    'max_pages' => 50,
    'max_depth' => 5,
    'rate_limit_ms' => 250,
    'queue_only' => false,
    'respect_robots_txt' => true,
    'retry_times' => 1,
    'retry_sleep' => 250,
]
```

Recommended production posture:

```php
'crawler' => [
    'queue_only' => true,
    'allowed_hosts' => ['example.com'],
    'check_external_links' => false,
]
```

Use synchronous `lazy-seo:crawl` only for local/manual checks. Use `lazy-seo:crawl-queue` or `lazy-seo:monitor --queue` for scheduled or large scans.


## Crawler SSRF hardening

The crawler blocks private network targets by default. This includes direct private IPs, loopback IPv6, userinfo URLs and IPv4 numeric tricks such as decimal, octal and hexadecimal host notation.

Keep this disabled unless you intentionally crawl internal services:

```env
LAZY_SEO_CRAWLER_ALLOW_PRIVATE_NETWORKS=false
```

Recommended production setup:

```php
'crawler' => [
    'allow_private_networks' => false,
    'allowed_hosts' => ['example.com'],
    'blocked_hosts' => [],
    'max_redirects' => 5,
    'max_body_kb' => 1024,
],
```

Redirect targets are checked with the same policy, so a public URL cannot redirect the crawler into private infrastructure.
