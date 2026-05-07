# Lazy SEO Tools

Production-focused SEO toolkit for Laravel 11/12/13.

Lazy SEO Tools helps you manage page meta, model SEO, redirects, sitemaps, SEO scans, monitoring, IndexNow submissions, content analysis and JSON-LD from one Laravel package.

Built with `spatie/laravel-package-tools`.

## Requirements

- PHP `^8.2`
- Laravel `^11.0` or `^12.0` or `^13.0`
- Livewire `^3.6` or `^4.0`
- `spatie/laravel-sitemap`
- `spatie/laravel-translatable`
- `intervention/image`

## Installation

```bash
composer require step2dev/lazy-seo-tools
```

Publish the config:

```bash
php artisan vendor:publish --tag="lazy-seo-config"
```

Publish migrations:

```bash
php artisan vendor:publish --tag="lazy-seo-migrations"
```

Run migrations:

```bash
php artisan migrate
```

The package is auto-discovered by Laravel through:

```php
Step2dev\LazySeoTools\LazySeoServiceProvider::class
```

## Quick start

Render SEO tags in your layout:

```blade
<head>
    {!! seo()->renderMetaTags() !!}
</head>
```

Set SEO data for the current response:

```php
seo()
    ->title('Laravel SEO Tools')
    ->description('SEO toolkit for Laravel applications')
    ->canonical(url()->current())
    ->image(asset('storage/og/seo-tools.jpg'))
    ->type('article')
    ->robots(['index', 'follow']);
```

You can also resolve and render directly:

```php
$data = seo()->resolve(url: request()->path());

return seo()->renderMetaTags();
```

## Configuration

The config file is published to:

```text
config/lazy-seo.php
```

### Table names

Table names are configured directly in the published config file and intentionally do **not** use `env()`.

```php
'tables' => [
    'seo' => 'seo',
    'seo_redirects' => 'seo_redirects',
    'seo_templates' => 'seo_templates',
    'seo_scans' => 'seo_scans',
    'seo_scan_issues' => 'seo_scan_issues',
    'seo_indexing_logs' => 'seo_indexing_logs',
],
```

Change them before running migrations if your application needs custom names.

Runtime settings like routes, sitemap path, crawler limits, queue settings, alerts, IndexNow and AI token may use `env()` inside the config.

## Blade usage

### Render all meta tags

```blade
{!! seo()->renderMetaTags() !!}
```

### Components

```blade
<x-lazy-seo-title />
<x-lazy-seo-meta />
<x-lazy-seo-og />
<x-lazy-seo-twitter />
<x-lazy-seo-jsonld type="article" :data="$schema" />
```

Supported component aliases:

```blade
<x-lazy-seo-schema type="article" :data="$schema" />
<x-seo::json-ld type="article" :data="$schema" />
<x-seo::schema type="article" :data="$schema" />
```

Package views are loaded under the `lazy-seo` namespace.

## Model SEO

Add the trait to an Eloquent model:

```php
use Illuminate\Database\Eloquent\Model;
use Step2dev\LazySeoTools\Concerns\HasSeo;

class Post extends Model
{
    use HasSeo;
}
```

Save SEO data:

```php
$post->updateSeo([
    'title' => ['en' => 'Post title', 'uk' => 'Назва статті'],
    'description' => ['en' => 'Post description', 'uk' => 'Опис статті'],
    'keywords' => ['en' => 'laravel, seo'],
    'canonical_url' => route('posts.show', $post),
    'robots' => ['index', 'follow'],
    'indexable' => true,
]);
```

Resolve SEO data:

```php
$data = $post->resolvedSeo();

$data->title;
$data->description;
$data->robotsContent();
```

Array output:

```php
$seo = $post->seoData();
```

## URL SEO

Create SEO data for a URL:

```php
use Step2dev\LazySeoTools\Models\Seo;

Seo::query()->create([
    'url' => '/pricing',
    'title' => ['en' => 'Pricing'],
    'description' => ['en' => 'Simple pricing for your product'],
    'robots' => ['index', 'follow'],
    'indexable' => true,
]);
```

Resolve URL SEO:

```php
$seo = seo()->forUrl('/pricing');
$data = seo()->resolve(url: '/pricing');
```

## Resolver priority

`SeoManager::resolve()` merges data in this order:

1. config defaults;
2. URL SEO;
3. model SEO;
4. template data;
5. fluent API values;
6. manual overrides.

Example:

```php
$data = seo()
    ->title('Manual title')
    ->resolve(model: $post, url: '/blog/example', overrides: [
        'description' => 'Custom description',
    ]);
```

## SEO templates

SEO templates are stored in the `seo_templates` table.

Supported translatable fields:

- `title`
- `description`
- `keywords`

Placeholders use `{key}` syntax.

```php
use Step2dev\LazySeoTools\Models\SeoTemplate;

SeoTemplate::query()->create([
    'name' => 'post',
    'title' => ['en' => '{title} | {site_name}'],
    'description' => ['en' => '{excerpt}'],
    'payload' => [
        'type' => 'article',
    ],
    'enabled' => true,
]);
```

Use a template:

```php
seo()->template('post', [
    'title' => $post->title,
    'excerpt' => $post->excerpt,
]);
```

## Redirects

The package includes `HandleSeoRedirects` middleware.

Register it in your application middleware stack or route middleware:

```php
use Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects;
```

Example in `bootstrap/app.php`:

```php
->withMiddleware(function ($middleware) {
    $middleware->web(append: [
        \Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects::class,
    ]);
})
```

Create a redirect:

```php
use Step2dev\LazySeoTools\Models\SeoRedirect;

SeoRedirect::query()->create([
    'old_url' => '/old-page',
    'new_url' => '/new-page',
    'status_code' => 301,
    'enabled' => true,
]);
```

Supported status codes:

- `301`
- `302`
- `307`
- `308`
- `410`

Supported redirect types:

```php
// Exact
'old_url' => '/old-page'

// Wildcard
'old_url' => '/blog/*'

// Regex
'old_url' => '#^old/(post-[0-9]+)$#',
'is_regex' => true,
'new_url' => '/new/$1'
```

### Redirect CSV import/export

Import:

```bash
php artisan lazy-seo:redirects-import redirects.csv
```

Import without updating existing rows:

```bash
php artisan lazy-seo:redirects-import redirects.csv --no-update
```

Export:

```bash
php artisan lazy-seo:redirects-export redirects.csv
```

CSV format:

```csv
old_url,new_url,status_code,enabled,is_regex
old-page,/new-page,301,1,0
#^old/(post-[0-9]+)$#,/new/$1,301,1,1
removed-page,,410,1,0
```

## Sitemap

Generate sitemap files:

```bash
php artisan lazy-seo:sitemap
```

Custom public path:

```bash
php artisan lazy-seo:sitemap --path=sitemaps/sitemap.xml
```

Warm sitemap cache:

```bash
php artisan lazy-seo:sitemap --warm
php artisan lazy-seo:sitemap:warm
```

Clear cache before generating:

```bash
php artisan lazy-seo:sitemap --clear-cache
php artisan lazy-seo:sitemap:warm --clear
```

JSON output:

```bash
php artisan lazy-seo:sitemap --json
```

### Sitemap config

```php
'sitemap' => [
    'path' => 'sitemap.xml',
    'index_path' => 'sitemap.xml',
    'chunk_size' => 50000,
    'gzip' => false,
    'force_index' => false,
    'exclude' => [
        'admin/*',
        'nova/*',
        'horizon/*',
        'telescope/*',
    ],
    'static_urls' => [
        [
            'loc' => '/',
            'changefreq' => 'daily',
            'priority' => 1.0,
        ],
    ],
    'models' => [
        App\Models\Post::class => [
            'enabled' => true,
            'url' => 'getSeoUrl',
            'scope' => 'published',
            'lastmod_column' => 'updated_at',
            'changefreq' => 'weekly',
            'priority' => 0.8,
            'images' => 'getSeoImages',
            'alternates' => 'getSeoAlternates',
        ],
    ],
],
```

When URL count exceeds `chunk_size`, the package writes sitemap chunks and a sitemap index automatically.

## SEO analyzer

Analyze prepared page data:

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

Checks include:

- title length;
- meta description length;
- canonical URL;
- robots directives;
- content length;
- H1/H2 structure;
- missing image alt attributes;
- internal/external links;
- OpenGraph readiness;
- Twitter card readiness;
- JSON-LD presence.

## Site crawler

Run a crawl from CLI:

```bash
php artisan lazy-seo:crawl https://example.com
```

Limit pages:

```bash
php artisan lazy-seo:crawl https://example.com --max-pages=100
```

Check external links:

```bash
php artisan lazy-seo:crawl https://example.com --check-external --max-external-links=100
```

Save JSON report:

```bash
php artisan lazy-seo:crawl https://example.com --output=storage/app/seo-report.json
```

## SEO monitoring

Run a monitoring scan and save results to the database:

```bash
php artisan lazy-seo:monitor https://example.com
```

Use configured URL:

```bash
php artisan lazy-seo:monitor
```

Queue a scan:

```bash
php artisan lazy-seo:monitor https://example.com --queue
```

Use custom queue connection/name:

```bash
php artisan lazy-seo:monitor https://example.com --queue --connection=redis --queue=seo
```

Fail CI/deployment when score is too low:

```bash
php artisan lazy-seo:monitor https://example.com --fail-under=80
```

Create a pending scan and dispatch it:

```bash
php artisan lazy-seo:crawl-queue https://example.com --queue=seo
```

## SEO history

Show scan history summary:

```bash
php artisan lazy-seo:history
```

Filter by URL/domain:

```bash
php artisan lazy-seo:history https://example.com --limit=20
```

JSON output:

```bash
php artisan lazy-seo:history https://example.com --json
```

## Content intelligence

Analyze an HTML file:

```bash
php artisan lazy-seo:content storage/app/page.html
```

With target keywords:

```bash
php artisan lazy-seo:content storage/app/page.html --keywords="laravel,seo,package"
```

With base URL:

```bash
php artisan lazy-seo:content storage/app/page.html --base-url=https://example.com
```

JSON output:

```bash
php artisan lazy-seo:content storage/app/page.html --json
```

Content intelligence checks:

- word count;
- headings;
- readability;
- keyword density;
- image alt attributes;
- internal links;
- external links;
- suggestions and warnings.

## IndexNow

Enable IndexNow in config:

```php
'indexnow' => [
    'enabled' => true,
    'key' => env('LAZY_SEO_INDEXNOW_KEY'),
    'key_location' => env('LAZY_SEO_INDEXNOW_KEY_LOCATION'),
    'host' => env('LAZY_SEO_INDEXNOW_HOST'),
],
```

Submit URLs:

```bash
php artisan lazy-seo:indexnow https://example.com/page-1 https://example.com/page-2
```

Submit URLs from file:

```bash
php artisan lazy-seo:indexnow --file=urls.txt
```

Submit configured sitemap URL:

```bash
php artisan lazy-seo:indexnow --sitemap
```

Override key/endpoint:

```bash
php artisan lazy-seo:indexnow https://example.com/page --key=YOUR_KEY --endpoint=https://api.indexnow.org/indexnow
```

Disable database logging for this command:

```bash
php artisan lazy-seo:indexnow https://example.com/page --no-log
```

## JSON-LD / Schema.org

Blade component:

```blade
<x-lazy-seo-jsonld type="article" :data="[
    'title' => $post->title,
    'description' => $post->excerpt,
    'image' => $post->cover_url,
    'url' => route('posts.show', $post),
]" />
```

Helper returning array schema:

```php
$schema = seo_schema('article', [
    'title' => $post->title,
    'description' => $post->excerpt,
]);
```

Helper returning script tag:

```php
echo seo_jsonld('article', [
    'title' => $post->title,
]);
```

Supported schema types depend on `SchemaService` / `JsonLdService`, including common types such as:

- Article
- BlogPosting
- Product
- Organization
- LocalBusiness
- WebSite
- BreadcrumbList
- FAQPage
- WebPage

## OpenGraph image generation

The package includes `OGImageService` and uses Intervention Image v3.

Config:

```php
'og_image' => [
    'disk' => 'public',
    'directory' => 'og',
    'width' => 1200,
    'height' => 630,
],
```

Use the service from the container:

```php
use Step2dev\LazySeoTools\Services\OGImageService;

$path = app(OGImageService::class)->generate([
    'title' => 'Laravel SEO Tools',
]);
```

## Optional web admin routes

Web routes are disabled by default.

Enable them in `config/lazy-seo.php`:

```php
'routes' => [
    'web' => true,
    'admin_prefix' => 'lazy-seo',
    'admin_middleware' => ['web', 'auth'],
],
```

Available pages:

```text
/lazy-seo/dashboard
/lazy-seo/issues
/lazy-seo/scans/{scan}
/lazy-seo/redirects
```

Named routes:

```text
lazy-seo.dashboard
lazy-seo.issues
lazy-seo.scans.show
lazy-seo.redirects
```

## Optional API routes

API routes are disabled by default.

Enable them in config:

```php
'routes' => [
    'api' => true,
    'api_prefix' => 'seo',
    'api_middleware' => ['api'],
    'api_write_middleware' => ['auth:sanctum'],
],
```

Routes:

```text
GET    /seo
GET    /seo/{seo}
POST   /seo
PUT    /seo/{seo}
DELETE /seo/{seo}
```

Write routes use `api_write_middleware`.

## Livewire components

The package registers these Livewire components when Livewire exists:

```blade
<livewire:lazy-seo-form />
<livewire:lazy-seo-analyzer />
<livewire:lazy-seo-redirect-table />
<livewire:lazy-seo-monitoring-dashboard />
<livewire:lazy-seo-issues-table />
<livewire:lazy-seo-scan-detail :scan="$scan" />
```

## Queue configuration

Queue settings:

```php
'queue' => [
    'enabled' => true,
    'connection' => env('LAZY_SEO_QUEUE_CONNECTION'),
    'queue' => env('LAZY_SEO_QUEUE_NAME', 'default'),
    'chunk_pages' => 25,
    'tries' => 2,
    'timeout' => 600,
],
```

Recommended for production:

```env
QUEUE_CONNECTION=redis
LAZY_SEO_QUEUE_NAME=seo
```

Then run a worker:

```bash
php artisan queue:work redis --queue=seo
```

## Scheduled monitoring

Set a cron expression in config/env:

```env
LAZY_SEO_MONITORING_SCHEDULE="0 */6 * * *"
LAZY_SEO_MONITORING_URL="https://example.com"
```

If `scheduled_queue` is enabled, the scheduled command dispatches scans to queue:

```env
LAZY_SEO_MONITORING_SCHEDULED_QUEUE=true
```

Make sure Laravel scheduler is running:

```bash
php artisan schedule:work
```

or via cron:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Alerts

Alerts are controlled by:

```php
'alerts' => [
    'enabled' => false,
    'score_threshold' => 75,
    'critical_issues_threshold' => 1,
    'new_issues_threshold' => 1,
    'failed_scans' => true,
    'cooldown_minutes' => 60,
    'webhook_url' => env('LAZY_SEO_ALERT_WEBHOOK_URL'),
],
```

## Commands

```bash
php artisan lazy-seo:about
php artisan lazy-seo:sitemap
php artisan lazy-seo:sitemap:warm
php artisan lazy-seo:redirects-import redirects.csv
php artisan lazy-seo:redirects-export redirects.csv
php artisan lazy-seo:crawl https://example.com
php artisan lazy-seo:crawl-queue https://example.com
php artisan lazy-seo:monitor https://example.com
php artisan lazy-seo:history
php artisan lazy-seo:indexnow https://example.com/page
php artisan lazy-seo:content storage/app/page.html
```

## Facades and helpers

Facades:

```php
use Step2dev\LazySeoTools\Facades\Seo;
use Step2dev\LazySeoTools\Facades\LazySeo;
```

Helpers:

```php
seo();
seo_schema('article', []);
seo_jsonld('article', []);
```

## Testing in package development

```bash
composer install
vendor/bin/pest
vendor/bin/pint
vendor/bin/phpstan analyse
```

## Notes for production

Recommended setup:

- enable redirect middleware only after redirects are reviewed;
- protect admin routes with `auth` or custom middleware;
- protect API write routes with Sanctum, Passport or custom auth;
- use Redis queue for crawler/monitoring jobs;
- set crawl page limits in production;
- enable external link checks carefully because they make real HTTP requests;
- configure sitemap cache for large sites;
- keep table names stable after migrations.

## License

MIT.
