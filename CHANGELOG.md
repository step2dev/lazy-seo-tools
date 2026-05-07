
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
