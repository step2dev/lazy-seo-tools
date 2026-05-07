# Lazy SEO Tools

Laravel SEO toolkit built on `spatie/laravel-package-tools`.

## Install

```bash
composer require step2dev/lazy-seo-tools
php artisan vendor:publish --tag="lazy-seo-config"
php artisan migrate
```

## Configurable tables

Table names are configured directly in `config/lazy-seo.php` and intentionally do not use `env()`:

```php
'tables' => [
    'seo' => 'seo',
    'seo_redirects' => 'seo_redirects',
    'seo_templates' => 'seo_templates',
],
```

Runtime options like routes, sitemap cache, redirects, OG image settings, webhooks and AI token may use `env()` inside config.

## Fluent API

```php
seo()
    ->title('Page title')
    ->description('Page description')
    ->canonical('/page')
    ->image('/storage/og/page.jpg')
    ->type('article');
```

Render in Blade:

```blade
{!! seo()->renderMetaTags() !!}
```

## Blade components

```blade
<x-lazy-seo-meta />
<x-lazy-seo-title />
<x-lazy-seo-og />
<x-lazy-seo-twitter />
<x-lazy-seo-jsonld :data="$schema" />
```

Anonymous package views are also available:

```blade
<x-seo::meta />
<x-seo::og />
<x-seo::twitter />
<x-seo::jsonld :data="$schema" />
```

## Model SEO

```php
use Step2dev\LazySeoTools\Concerns\HasSeo;

class Post extends Model
{
    use HasSeo;
}
```

```php
$post->updateSeo([
    'title' => ['en' => 'Post title'],
    'description' => ['en' => 'Post description'],
]);

$data = $post->resolvedSeo();
```

## Resolver priority

`SeoManager::resolve()` uses this order:

1. config defaults;
2. URL SEO;
3. model SEO;
4. templates/fluent API/manual overrides.

```php
$data = seo()
    ->title('Manual title')
    ->resolve(model: $post, url: '/blog/post');
```

## Sitemap

```bash
php artisan lazy-seo:sitemap
```

## Redirects

Enable middleware manually in your app if needed:

```php
use Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects;
```

Supports exact URLs, wildcard URLs, 301/302/307/308, 410 Gone, hit counters and loop protection.

## Tests

```bash
composer install
vendor/bin/pest
```

## Sitemap v2

Generate sitemap files from SEO records, static URLs, and configured Eloquent models:

```bash
php artisan lazy-seo:sitemap
php artisan lazy-seo:sitemap --path=sitemaps/sitemap.xml
```

Config options:

```php
'sitemap' => [
    'path' => 'sitemap.xml',
    'index_path' => 'sitemap.xml',
    'chunk_size' => 50000,
    'gzip' => false,
    'force_index' => false,
    'exclude' => ['admin/*'],
    'static_urls' => [
        ['loc' => '/', 'changefreq' => 'daily', 'priority' => 1.0],
    ],
    'models' => [
        App\Models\Post::class => [
            'enabled' => true,
            'url' => 'getSeoUrl',
            'scope' => 'published',
            'lastmod_column' => 'updated_at',
            'changefreq' => 'weekly',
            'priority' => 0.8,
        ],
    ],
],
```

When the URL count exceeds `chunk_size`, the package writes sitemap chunks and a sitemap index automatically.

## Redirects v2

Supported redirect types:

- exact: `old-page` → `/new-page`
- wildcard: `blog/*` → `/articles`
- regex: `#^old/(post-[0-9]+)$#` → `/new/$1`
- gone: `410`

CSV import/export:

```bash
php artisan lazy-seo:redirects-import redirects.csv
php artisan lazy-seo:redirects-export redirects.csv
```

CSV columns:

```csv
old_url,new_url,status_code,enabled,is_regex
old-page,/new-page,301,1,0
#^old/(post-[0-9]+)$#,/new/$1,301,1,1
```


## Analyzer v1

```php
use Step2dev\LazySeoTools\Services\SeoAnalyzerService;

$result = app(SeoAnalyzerService::class)->analyzePage([
    'title' => 'Laravel SEO Tools',
    'description' => 'Production SEO toolkit for Laravel applications.',
    'canonical_url' => 'https://example.com/page',
    'robots' => ['index', 'follow'],
    'image' => 'https://example.com/og.jpg',
    'html' => $html,
]);

$result->score;
$result->grade();
$result->toArray();
```

Analyzer checks:

- title and meta description length;
- canonical URL;
- robots directives;
- content length;
- H1/H2 structure;
- missing image alt attributes;
- internal/external links;
- OpenGraph/Twitter readiness;
- JSON-LD presence.

## Schema.org / JSON-LD

```blade
<x-lazy-seo-jsonld type="article" :data="[
    'title' => $post->title,
    'description' => $post->excerpt,
    'image' => $post->cover_url,
    'author' => $post->author->name,
    'url' => route('posts.show', $post),
]" />
```

Supported schema types:

- `webPage`
- `article`
- `blogPosting`
- `product`
- `organization`
- `localBusiness`
- `webSite`
- `breadcrumbs`
- `faq`

Programmatic usage:

```php
seo_schema('breadcrumbs', [
    'items' => [
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Blog', 'url' => route('blog.index')],
    ],
]);

seo_jsonld('faq', [
    'items' => [
        ['question' => 'What is Lazy SEO?', 'answer' => 'Laravel SEO toolkit.'],
    ],
]);
```

## Site Crawler / SEO Scanner

```bash
php artisan lazy-seo:crawl https://example.com --max-pages=100 --output=storage/app/seo-report.json
```

Programmatic usage:

```php
use Step2dev\LazySeoTools\Services\SiteCrawlerService;

$result = app(SiteCrawlerService::class)->crawl('https://example.com', [
    'max_pages' => 100,
]);

$result->score();
$result->brokenLinks;
$result->duplicateTitles;
$result->canonicalConflicts;
```

Crawler config:

```php
'crawler' => [
    'enabled' => env('LAZY_SEO_CRAWLER_ENABLED', true),
    'max_pages' => (int) env('LAZY_SEO_CRAWLER_MAX_PAGES', 50),
    'timeout' => (int) env('LAZY_SEO_CRAWLER_TIMEOUT', 10),
    'user_agent' => env('LAZY_SEO_CRAWLER_USER_AGENT', 'LazySeoBot/1.0'),
    'respect_noindex' => env('LAZY_SEO_CRAWLER_RESPECT_NOINDEX', false),
    'exclude' => ['admin/*', 'nova/*', 'horizon/*', 'telescope/*'],
],
```

Table names are still configured directly in `config/lazy-seo.php` and do not use `env()`.

## SEO Monitoring

Run a crawl and store a persistent SEO snapshot:

```bash
php artisan lazy-seo:monitor https://example.com --max-pages=100
```

Enable package web routes when you want the bundled Livewire admin UI:

```env
LAZY_SEO_WEB_ROUTES=true
LAZY_SEO_ADMIN_PREFIX=lazy-seo
```

Admin pages:

- `/lazy-seo/dashboard`
- `/lazy-seo/issues`
- `/lazy-seo/redirects`

Schedule monitoring from config:

```env
LAZY_SEO_MONITORING_SCHEDULE="0 3 * * *"
LAZY_SEO_MONITORING_URL="https://example.com"
LAZY_SEO_MONITORING_MAX_PAGES=250
LAZY_SEO_MONITORING_FAIL_UNDER=75
```

Monitoring tables are configurable in `config/lazy-seo.php` without `env()`:

```php
'tables' => [
    'seo' => 'seo',
    'seo_redirects' => 'seo_redirects',
    'seo_templates' => 'seo_templates',
    'seo_scans' => 'seo_scans',
    'seo_scan_issues' => 'seo_scan_issues',
],
```

## IndexNow

```bash
php artisan lazy-seo:indexnow https://example.com/posts/my-post
php artisan lazy-seo:indexnow --sitemap
php artisan lazy-seo:indexnow --file=urls.txt
```

Configure it in `config/lazy-seo.php`:

```php
'indexnow' => [
    'enabled' => env('LAZY_SEO_INDEXNOW_ENABLED', false),
    'key' => env('LAZY_SEO_INDEXNOW_KEY'),
    'endpoint' => env('LAZY_SEO_INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow'),
]
```

Indexing logs are stored in the table configured by `tables.seo_indexing_logs`.
Table names stay config-only and do not use `env()`.

## Content Intelligence

```php
$result = app(\Step2dev\LazySeoTools\Services\ContentIntelligenceService::class)
    ->analyze($html, ['laravel seo'], url('/'));

$result->score;
$result->warnings;
$result->suggestions;
$result->metrics;
```

CLI:

```bash
php artisan lazy-seo:content storage/app/page.html --keywords="laravel seo,seo tools"
php artisan lazy-seo:content storage/app/page.html --keywords="laravel seo" --json
```
