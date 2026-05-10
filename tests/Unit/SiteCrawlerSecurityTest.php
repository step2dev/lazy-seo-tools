<?php

use Illuminate\Support\Facades\Http;
use Step2dev\LazySeoTools\Services\SeoAnalyzerService;
use Step2dev\LazySeoTools\Services\SiteCrawlerService;
use Step2dev\LazySeoTools\Services\UrlNormalizer;

function crawlerForSecurityTests(): SiteCrawlerService
{
    return new SiteCrawlerService(app(SeoAnalyzerService::class), new UrlNormalizer);
}

it('blocks private network targets by default', function (): void {
    $crawler = crawlerForSecurityTests();
    $method = new ReflectionMethod($crawler, 'isUrlAllowed');
    $method->setAccessible(true);

    expect($method->invoke($crawler, 'http://127.0.0.1/admin', [
        'allow_private_networks' => false,
        'allowed_hosts' => [],
        'blocked_hosts' => [],
        'max_redirects' => 5,
        'max_body_kb' => 1024,
    ]))->toBeFalse();
});

it('respects explicit crawler host allowlist', function (): void {
    $crawler = crawlerForSecurityTests();
    $method = new ReflectionMethod($crawler, 'isUrlAllowed');
    $method->setAccessible(true);

    expect($method->invoke($crawler, 'https://laravel.com/docs', [
        'allow_private_networks' => false,
        'allowed_hosts' => ['example.com'],
        'blocked_hosts' => [],
        'max_redirects' => 5,
        'max_body_kb' => 1024,
    ]))->toBeFalse();
});

it('blocks non http schemes', function (): void {
    $crawler = crawlerForSecurityTests();
    $method = new ReflectionMethod($crawler, 'isUrlAllowed');
    $method->setAccessible(true);

    expect($method->invoke($crawler, 'file:///etc/passwd', [
        'allow_private_networks' => true,
        'allowed_hosts' => [],
        'blocked_hosts' => [],
        'max_redirects' => 5,
        'max_body_kb' => 1024,
        'retry_times' => 0,
        'retry_sleep' => 0,
    ]))->toBeFalse();
});

it('parses robots disallow rules', function (): void {
    $crawler = crawlerForSecurityTests();
    $method = new ReflectionMethod($crawler, 'parseRobotsDisallowRules');
    $method->setAccessible(true);

    expect($method->invoke($crawler, "User-agent: *\nDisallow: /private\nDisallow: /tmp/*\n"))
        ->toBe(['/private', '/tmp/*']);
});

it('checks robots rules against urls', function (): void {
    $crawler = crawlerForSecurityTests();
    $method = new ReflectionMethod($crawler, 'isAllowedByRobots');
    $method->setAccessible(true);

    expect($method->invoke($crawler, 'https://example.com/private/page', ['/private']))->toBeFalse()
        ->and($method->invoke($crawler, 'https://example.com/public/page', ['/private']))->toBeTrue();
});

it('does not crawl deeper than configured max depth', function (): void {
    Http::fake([
        'https://example.com/robots.txt' => Http::response('', 404),
        'https://example.com/' => Http::response('<a href="/level-1">Level 1</a>', 200),
        'https://example.com/level-1' => Http::response('<a href="/level-2">Level 2</a>', 200),
        'https://example.com/level-2' => Http::response('<title>Level 2</title>', 200),
    ]);

    $result = crawlerForSecurityTests()->crawl('https://example.com', [
        'max_pages' => 10,
        'max_depth' => 1,
        'rate_limit_ms' => 0,
        'allow_private_networks' => true,
    ]);

    expect(array_map(static fn ($page): string => $page->url, $result->pages))
        ->toContain('https://example.com/')
        ->toContain('https://example.com/level-1')
        ->not->toContain('https://example.com/level-2');
});

it('truncates oversized response bodies before parsing', function (): void {
    $crawler = crawlerForSecurityTests();
    $method = new ReflectionMethod($crawler, 'limitedBody');
    $method->setAccessible(true);

    expect(strlen($method->invoke($crawler, str_repeat('a', 4096), 1)))->toBe(1024);
});

it('blocks responses with content length above crawler limit before parsing', function (): void {
    Http::fake([
        'https://example.com/robots.txt' => Http::response('', 404),
        'https://example.com/' => Http::response(str_repeat('a', 2048), 200, [
            'Content-Length' => '2048',
            'Content-Type' => 'text/html',
        ]),
    ]);

    $result = crawlerForSecurityTests()->crawl('https://example.com', [
        'max_pages' => 1,
        'max_body_kb' => 1,
        'rate_limit_ms' => 0,
        'allow_private_networks' => true,
    ]);

    expect($result->pages[0]->error)->toBe('Response exceeds crawler max body size.');
});

it('blocks non html crawler responses before parsing', function (): void {
    Http::fake([
        'https://example.com/robots.txt' => Http::response('', 404),
        'https://example.com/' => Http::response('{"ok": true}', 200, [
            'Content-Type' => 'application/json',
        ]),
    ]);

    $result = crawlerForSecurityTests()->crawl('https://example.com', [
        'max_pages' => 1,
        'rate_limit_ms' => 0,
        'allow_private_networks' => true,
    ]);

    expect($result->pages[0]->error)->toBe('Unsupported crawler response content type.');
});
