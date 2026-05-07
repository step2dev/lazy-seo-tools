# Lazy SEO Tools docs

## Architecture

The package is based on `spatie/laravel-package-tools` through `LazySeoServiceProvider`.

Registered package assets:

- config: `config/lazy-seo.php`
- views: `resources/views`
- translations: `resources/lang`
- migrations: SEO, redirects, templates
- commands: `lazy-seo:about`, `lazy-seo:sitemap`
- routes: `web`, `api` loaded by package-tools but internally disabled by config by default

## Main classes

- `Step2dev\LazySeoTools\Services\SeoManager`
- `Step2dev\LazySeoTools\Services\SitemapGeneratorService`
- `Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects`
- `Step2dev\LazySeoTools\Concerns\HasSeo`
- `Step2dev\LazySeoTools\Models\Seo`
- `Step2dev\LazySeoTools\Models\SeoRedirect`

## Facades

- `Seo`
- `LazySeo`

Both resolve `SeoManager`.
