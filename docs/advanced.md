# Advanced features

## Site crawler

Run a crawl:

```bash
php artisan lazy-seo:crawl https://example.com
```

Limit pages and optionally check external links:

```bash
php artisan lazy-seo:crawl https://example.com --max-pages=100
php artisan lazy-seo:crawl https://example.com --check-external --max-external-links=100
```

Save a JSON report:

```bash
php artisan lazy-seo:crawl https://example.com --output=storage/app/seo-report.json
```

## SEO monitoring

Run a monitoring scan and store results in the database:

```bash
php artisan lazy-seo:monitor https://example.com
```

Use the configured URL from `lazy-seo.monitoring.url`:

```bash
php artisan lazy-seo:monitor
```

Queue a scan:

```bash
php artisan lazy-seo:monitor https://example.com --queue
php artisan lazy-seo:monitor https://example.com --queue --connection=redis --queue-name=seo
```

Fail CI/deployments when score is too low:

```bash
php artisan lazy-seo:monitor https://example.com --fail-under=80
```

Create a pending scan and dispatch it:

```bash
php artisan lazy-seo:crawl-queue https://example.com --queue=seo
```

## SEO history

```bash
php artisan lazy-seo:history
php artisan lazy-seo:history https://example.com --limit=20
php artisan lazy-seo:history https://example.com --json
```

## Content intelligence

Analyze an HTML file:

```bash
php artisan lazy-seo:content storage/app/page.html
```

Use target keywords and a base URL:

```bash
php artisan lazy-seo:content storage/app/page.html --keywords="laravel,seo,package" --base-url=https://example.com
```

Output JSON:

```bash
php artisan lazy-seo:content storage/app/page.html --json
```

Checks include word count, headings, readability, keyword density, image alt attributes, internal links, external links, suggestions and warnings.

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

Submit URLs from a file or the configured sitemap URL:

```bash
php artisan lazy-seo:indexnow --file=urls.txt
php artisan lazy-seo:indexnow --sitemap
```

Override request details:

```bash
php artisan lazy-seo:indexnow https://example.com/page --key=YOUR_KEY --endpoint=https://api.indexnow.org/indexnow
```

Disable database logging for one submission:

```bash
php artisan lazy-seo:indexnow https://example.com/page --no-log
```

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

## OpenGraph image generation

The package includes `OGImageService` and uses Intervention Image v3.

```php
use Step2dev\LazySeoTools\Services\OGImageService;

$path = app(OGImageService::class)->generate([
    'title' => 'Laravel SEO Tools',
]);
```

Relevant config:

```php
'og_image' => [
    'disk' => 'public',
    'directory' => 'og',
    'width' => 1200,
    'height' => 630,
],
```

## Optional API routes

API routes are disabled by default:

```php
'features' => [
    'api' => true,
],

'routes' => [
    'api' => true,
    'api_prefix' => 'seo',
    'api_middleware' => ['api'],
    'api_read_middleware' => [],
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

Read routes can be protected with `api_read_middleware`. Write routes use `api_write_middleware`.

## Queue and scheduled monitoring

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

Recommended production environment:

```env
QUEUE_CONNECTION=redis
LAZY_SEO_QUEUE_NAME=seo
LAZY_SEO_MONITORING_SCHEDULE="0 */6 * * *"
LAZY_SEO_MONITORING_URL="https://example.com"
LAZY_SEO_MONITORING_SCHEDULED_QUEUE=true
```

Run a worker:

```bash
php artisan queue:work redis --queue=seo
```

Make sure Laravel scheduler is running:

```bash
php artisan schedule:work
```
