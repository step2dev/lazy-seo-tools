<?php

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
    $this->get('/lazy-seo/dashboard')->assertOk();
});
