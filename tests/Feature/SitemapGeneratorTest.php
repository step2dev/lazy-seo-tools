<?php

use Step2dev\LazySeoTools\Models\Seo;
use Step2dev\LazySeoTools\Services\SitemapGeneratorService;

it('generates sitemap from seo records', function () {
    Seo::create([
        'url' => '/about',
        'title' => ['en' => 'About'],
        'indexable' => true,
    ]);

    $path = app(SitemapGeneratorService::class)->generate(path: 'test-sitemap.xml');

    expect($path)->toEndWith('test-sitemap.xml')
        ->and(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toContain('https://example.com/about');
});

it('generates sitemap index when urls exceed configured chunk size', function () {
    config()->set('lazy-seo.sitemap.chunk_size', 1);
    config()->set('lazy-seo.sitemap.force_index', true);

    $service = app(SitemapGeneratorService::class);
    $path = $service->generate([
        ['loc' => '/first'],
        ['loc' => '/second'],
    ], 'chunked-sitemap.xml');

    expect($path)->toEndWith('chunked-sitemap.xml')
        ->and(file_exists($path))->toBeTrue()
        ->and(file_get_contents($path))->toContain('<sitemapindex')
        ->and(file_exists(public_path('chunked-sitemap-1.xml')))->toBeTrue()
        ->and(file_exists(public_path('chunked-sitemap-2.xml')))->toBeTrue();
});

it('excludes configured sitemap paths', function () {
    config()->set('lazy-seo.sitemap.exclude', ['admin/*']);

    $path = app(SitemapGeneratorService::class)->generate([
        ['loc' => '/admin/dashboard'],
        ['loc' => '/public-page'],
    ], 'exclude-sitemap.xml');

    expect(file_get_contents($path))
        ->not->toContain('/admin/dashboard')
        ->toContain('/public-page');
});
