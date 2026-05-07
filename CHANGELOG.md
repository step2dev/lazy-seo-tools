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
