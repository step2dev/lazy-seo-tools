<?php

use Step2dev\LazySeoTools\Services\SeoManager;

it('registers the seo manager singleton', function (): void {
    expect(app(SeoManager::class))->toBeInstanceOf(SeoManager::class);
    expect(app('lazy-seo'))->toBe(app(SeoManager::class));
});

it('publishes package routes when enabled', function (): void {
    $this->get('/lazy-seo/dashboard')->assertOk();
});
