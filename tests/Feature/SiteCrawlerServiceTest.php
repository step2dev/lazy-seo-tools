<?php

use Illuminate\Support\Facades\Http;
use Step2dev\LazySeoTools\Services\SiteCrawlerService;

it('collects broken external links when enabled', function (): void {
    Http::fake([
        'example.com/*' => Http::response('<html><head><title>Valid page title for crawler</title><meta name="description" content="This is a valid meta description long enough for crawler testing."><link rel="canonical" href="https://example.com/"></head><body><h1>Home</h1><a href="https://external.test/missing">External</a></body></html>', 200),
        'external.test/*' => Http::response('', 404),
    ]);

    $result = app(SiteCrawlerService::class)->crawl('https://example.com', [
        'max_pages' => 1,
        'check_external_links' => true,
        'allow_private_networks' => true,
    ]);

    expect($result->externalBrokenLinks)
        ->toHaveKey('https://external.test/missing')
        ->and($result->toArray()['external_broken_links'])
        ->toHaveKey('https://external.test/missing');
});
