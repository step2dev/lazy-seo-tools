<?php

use Illuminate\Support\Facades\Route;
use Step2dev\LazySeoTools\Services\SeoManager;

it('registers the seo manager singleton', function (): void {
    expect(app(SeoManager::class))->toBeInstanceOf(SeoManager::class);
    expect(app('lazy-seo'))->toBe(app(SeoManager::class));
});

it('renders meta tags through the helper', function (): void {
    $html = seo_meta(overrides: [
        'title' => 'Helper title',
        'description' => 'Helper description',
        'canonical_url' => 'https://example.com/helper',
    ]);

    expect((string) $html)
        ->toContain('<title>Helper title</title>')
        ->toContain('Helper description')
        ->toContain('<link rel="canonical" href="https://example.com/helper">');
});

it('publishes package routes when enabled', function (): void {
    config()->set('lazy-seo.routes.web', true);

    require __DIR__.'/../../routes/web.php';

    Route::getRoutes()->refreshNameLookups();

    expect(Route::has('lazy-seo.dashboard'))->toBeTrue()
        ->and(Route::has('lazy-seo.issues'))->toBeTrue()
        ->and(Route::has('lazy-seo.redirects'))->toBeTrue();
});

it('merges advanced defaults behind the compact published config', function (): void {
    expect(config('lazy-seo.audit.severity_weights.error'))->toBe(8)
        ->and(config('lazy-seo.sitemap.chunk_size'))->toBe(50000)
        ->and(config('lazy-seo.redirects.allowed_status_codes'))->toBe([301, 302, 307, 308, 410]);
});
