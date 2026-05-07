# Lazy SEO Tools documentation

This package provides a Laravel SEO toolkit for meta tags, model SEO, redirects, sitemaps, site scans, monitoring, IndexNow, content intelligence, Livewire admin screens and JSON-LD.

## Start here

- [Documentation index](docs/index.md)
- [Installation](docs/install.md)
- [Quick start](docs/quick-start.md)
- [Blade components](docs/blade.md)
- [Facade and helpers](docs/facade.md)
- [Livewire components](docs/livewire.md)
- [Redirects](docs/redirects.md)
- [Sitemap](docs/sitemap.md)
- [Advanced features](docs/advanced.md)

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

## Production notes

- Keep table names stable after migrations.
- Enable web admin/API routes only when the application needs them.
- Protect admin routes with `auth` or custom middleware.
- Protect API write routes with Sanctum, Passport or custom middleware.
- Use queue workers for crawler and monitoring jobs on production sites.
- Set crawl page limits before enabling external link checks.
- Configure sitemap cache for large sites.
