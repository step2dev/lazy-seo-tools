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
