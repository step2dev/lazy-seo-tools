<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Step2dev\LazySeoTools\Services\SitemapGeneratorService;

it('generates image and hreflang sitemap entries without creating a duplicate engine', function (): void {
    config()->set('app.url', 'https://example.com');
    config()->set('lazy-seo.sitemap.path', 'lazy-seo-test-sitemap.xml');

    $file = app(SitemapGeneratorService::class)->generate([
        [
            'loc' => '/uk/posts/seo',
            'images' => [
                ['loc' => '/storage/seo.jpg', 'title' => 'SEO image', 'caption' => 'SEO caption'],
            ],
            'alternates' => [
                'uk' => '/uk/posts/seo',
                'en' => '/en/posts/seo',
            ],
        ],
    ]);

    $xml = File::get($file);

    expect($xml)
        ->toContain('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"')
        ->toContain('xmlns:xhtml="http://www.w3.org/1999/xhtml"')
        ->toContain('<image:loc>https://example.com/storage/seo.jpg</image:loc>')
        ->toContain('hreflang="en" href="https://example.com/en/posts/seo"');

    File::delete($file);
});

it('splits large sitemap files and writes an index', function (): void {
    config()->set('app.url', 'https://example.com');
    config()->set('lazy-seo.sitemap.path', 'lazy-seo-split.xml');
    config()->set('lazy-seo.sitemap.index_path', 'lazy-seo-split-index.xml');
    config()->set('lazy-seo.sitemap.chunk_size', 1);

    $result = app(SitemapGeneratorService::class)->generateFiles([
        ['loc' => '/first'],
        ['loc' => '/second'],
    ]);

    expect($result['index'])->not->toBeNull();
    expect($result['files'])->toHaveCount(2);
    expect(File::get($result['index']))->toContain('<sitemapindex');

    File::delete($result['index']);
    File::delete($result['files']);
});

it('can clear sitemap cache', function (): void {
    $service = app(SitemapGeneratorService::class);

    Cache::put($service->cacheKey(), 'cached-value', 60);

    expect(Cache::has($service->cacheKey()))->toBeTrue();
    expect($service->clearCache())->toBeTrue();
    expect(Cache::has($service->cacheKey()))->toBeFalse();
});
