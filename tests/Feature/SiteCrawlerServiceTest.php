<?php

use Illuminate\Support\Facades\Http;
use Step2dev\LazySeoTools\Services\SiteCrawlerService;

it('parses page seo signals', function (): void {
    $html = '<html><head><title>Laravel SEO Tools Complete Guide</title><meta name="description" content="A practical Laravel SEO tools package guide for production websites."><link rel="canonical" href="/guide"><meta property="og:title" content="OG"><meta name="twitter:title" content="Twitter"><script type="application/ld+json">{}</script></head><body><h1>Guide</h1><h2>Setup</h2><a href="/about">About</a><img src="/cover.jpg" alt="Cover">'.str_repeat(' content', 260).'</body></html>';

    $page = app(SiteCrawlerService::class)->parse('https://example.com/guide', 200, $html);

    expect($page->title)->toBe('Laravel SEO Tools Complete Guide')
        ->and($page->description)->toContain('Laravel SEO tools')
        ->and($page->canonical)->toBe('/guide')
        ->and($page->links[0]['url'])->toBe('https://example.com/about')
        ->and($page->images[0]['missing_alt'])->toBeFalse()
        ->and($page->analysis?->score)->toBeGreaterThan(70);
});

it('crawls internal pages and detects duplicate titles', function (): void {
    Http::fake([
        'https://example.com/' => Http::response('<html><head><title>Same SEO Title For Pages</title><meta name="description" content="A valid homepage description for SEO scanning."></head><body><h1>Home</h1><a href="/about">About</a>'.str_repeat(' content', 260).'</body></html>', 200),
        'https://example.com/about' => Http::response('<html><head><title>Same SEO Title For Pages</title><meta name="description" content="A valid about page description for SEO scanning."></head><body><h1>About</h1>'.str_repeat(' content', 260).'</body></html>', 200),
    ]);

    $result = app(SiteCrawlerService::class)->crawl('https://example.com/', ['max_pages' => 5]);

    expect($result->pages)->toHaveCount(2)
        ->and($result->duplicateTitles)->not->toBeEmpty();
});
