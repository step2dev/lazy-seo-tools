
## v8 - Analyzer and Schema.org

- Added `SeoAnalysisResult` immutable DTO.
- Rebuilt `SeoAnalyzerService` with score 0-100, errors, warnings, notices and metrics.
- Added Schema.org builder service.
- Added JSON-LD support for Article, BlogPosting, Product, Organization, LocalBusiness, WebSite, BreadcrumbList and FAQPage.
- Added `seo_schema()` and `seo_jsonld()` helpers.
- Added Analyzer and Schema tests.


## v7 - Sitemap v2 and Redirects v2

- Added sitemap index generation.
- Added sitemap chunking up to 50k URLs per file.
- Added optional gzip sitemap output.
- Added static URL, model source, and exclude support via config.
- Added regex redirects with backreferences.
- Added CSV import/export for redirects.
- Added redirect loop protection tests.

# Changelog

## Unreleased

### Added
- Immutable `SeoData` DTO with `fromArray()`, `defaults()`, `with()` and `merge()` helpers.
- Resolver priority support in `SeoManager::resolve()`.
- Optional resolved SEO cache via `lazy-seo.cache.resolved_minutes`.
- Twitter Blade component: `<x-lazy-seo-twitter />` and anonymous `<x-seo::twitter />` support.
- Tests for immutable DTO, resolver priority and Twitter component rendering.

### Changed
- `SeoManager` now keeps predictable priority: config defaults → URL SEO → model SEO → template/fluent/manual overrides.
- `HasSeo` now exposes `resolvedSeo()` returning `SeoData` while keeping `seoData()` array compatibility.
- Table names stay configurable only through published config values, without `env()`.

## v9 - Crawler / Site Scanner

- Added `SiteCrawlerService` for internal site crawling.
- Added `CrawlResult` and `CrawledPage` DTOs.
- Added URL normalization service.
- Added broken links, duplicate titles/descriptions, canonical conflicts, orphan pages and redirect-chain detection.
- Added `lazy-seo:crawl` command with optional JSON report output.
- Added crawler config without changing SEO table config behavior.
- Added crawler tests with HTTP fakes.

## v10 - SEO Monitoring + Admin UI

- Added SEO monitoring database snapshots.
- Added configurable `seo_scans` and `seo_scan_issues` table names via config only.
- Added `lazy-seo:monitor` command.
- Added scheduled monitoring support through `lazy-seo.monitoring.schedule`.
- Added `SeoMonitoringService`.
- Added `SeoScan` and `SeoScanIssue` models.
- Added Livewire admin dashboard and issues table.
- Added monitoring tests.

## v11 - IndexNow + Content Intelligence

- Added IndexNow URL submission service.
- Added `lazy-seo:indexnow` command with URL, file and sitemap modes.
- Added `seo_indexing_logs` table and model.
- Added Content Intelligence service for headings, readability, keyword density, images and internal links.
- Added `lazy-seo:content` command.
- Added tests for IndexNow and Content Intelligence.
