# step2dev/lazy-seo-tools

`step2dev/lazy-seo-tools` is a Laravel SEO toolkit for page meta tags, model SEO records, redirects, sitemaps, SEO scans, monitoring, IndexNow submissions, content intelligence and JSON-LD.

## Documentation

- [Installation](install.md)
- [Quick start](quick-start.md)
- [Blade components](blade.md)
- [Facade and helpers](facade.md)
- [Livewire components](livewire.md)
- [Redirects](redirects.md)
- [Sitemap](sitemap.md)
- [Advanced features](advanced.md)

## Core capabilities

- SEO records for URLs and Eloquent models.
- Translatable title, description and keywords through `spatie/laravel-translatable`.
- Fluent SEO manager through `seo()`, `Seo` and `LazySeo`.
- Blade helpers and components for meta, OpenGraph, Twitter cards and JSON-LD.
- Exact, wildcard and regex redirects with CSV import/export.
- Sitemap generation with static URLs, model sources, chunking, cache warming and optional gzip.

## Optional advanced layers

- Site crawler and SEO audit reports.
- SEO monitoring snapshots, history summaries and issue tracking.
- IndexNow submission and database logging.
- Content intelligence checks for HTML files.
- Livewire admin UI, optional web routes and optional headless API routes.

## Recommended first setup

1. Install the package and publish config/migrations.
2. Add `seo_meta()` to the main layout `<head>`.
3. Use the fluent API for simple pages.
4. Add `HasSeo` to models that need persisted SEO data.
5. Enable advanced layers like crawler, monitoring, IndexNow, Livewire admin or API routes only when the application needs them.
