# step2dev/lazy-seo-tools

Laravel SEO toolkit for page meta tags, model SEO, redirects, sitemaps, SEO scans, monitoring, IndexNow and JSON-LD.

## Start here

- [Quick start](quick-start.md)
- [Встановлення](install.md)
- [Blade компоненти](blade.md)
- [Livewire](livewire.md)
- [Redirects](redirects.md)
- [Facade](facade.md)
- [Sitemap](sitemap.md)

## Recommended first setup

1. Install the package and publish config/migrations.
2. Add `seo_meta()` or `{!! seo()->renderMetaTags() !!}` to your main layout.
3. Use the fluent API for simple pages.
4. Add `HasSeo` to models that need editable SEO.
5. Enable redirects, sitemap, monitoring or IndexNow only when the application needs them.
