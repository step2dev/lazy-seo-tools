# Release checklist

Use this checklist before tagging a beta or stable release.

## Local package checks

```bash
composer install
composer format
composer analyse
composer test
```

## Fresh Laravel application smoke test

Run against a clean app for every supported Laravel version.

```bash
composer create-project laravel/laravel lazy-seo-smoke
cd lazy-seo-smoke
composer config repositories.lazy-seo path ../lazy-seo-tools
composer require step2dev/lazy-seo-tools:@dev
php artisan vendor:publish --tag=lazy-seo-config
php artisan migrate
```

## Manual runtime flow

- render Blade meta components on a normal page;
- create a URL SEO record and resolve it by path;
- create a 301 redirect and verify the middleware response;
- generate `sitemap.xml`;
- run `php artisan lazy-seo:crawl https://example.test --max-pages=5 --max-depth=1`;
- run `php artisan lazy-seo:crawl-queue https://example.test` with a queue worker;
- enable admin routes and verify `auth` + `can:manage-lazy-seo` protection;
- enable API routes and verify write routes require `auth:sanctum`;
- test Livewire admin components on Livewire 3 and Livewire 4.

## Release gate

Do not tag a stable release if any of these are not true:

- crawler blocks private/reserved networks by default;
- crawler validates redirect targets manually;
- admin routes are protected by auth and gate middleware;
- API write routes are protected by auth middleware;
- AI is disabled by default and fails closed without a token;
- CI matrix passes for all supported PHP/Laravel combinations.
