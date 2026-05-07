<?php

use Illuminate\Support\Facades\Http;
use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Models\SeoScanIssue;
use Step2dev\LazySeoTools\Services\SeoMonitoringService;

it('stores crawl scan snapshots and issues', function (): void {
    Http::fake([
        'https://example.com/' => Http::response('<html><head><title>Home SEO Title</title><meta name="description" content="Valid homepage description for seo monitoring."><link rel="canonical" href="https://example.com/"></head><body><h1>Home</h1><a href="/missing">Missing</a><img src="/image.jpg">'.str_repeat(' content', 260).'</body></html>', 200),
        'https://example.com/missing' => Http::response('Not found', 404),
    ]);

    $scan = app(SeoMonitoringService::class)->scan('https://example.com/', ['max_pages' => 5]);

    expect($scan)->toBeInstanceOf(SeoScan::class)
        ->and($scan->pages_count)->toBe(2)
        ->and($scan->issues_count)->toBeGreaterThan(0)
        ->and(SeoScanIssue::query()->where('seo_scan_id', $scan->id)->exists())->toBeTrue();
});

it('uses configurable monitoring table names without env in table config', function (): void {
    config()->set('lazy-seo.tables.seo_scans', 'custom_seo_scans');
    config()->set('lazy-seo.tables.seo_scan_issues', 'custom_seo_scan_issues');

    $scan = new SeoScan;
    $issue = new SeoScanIssue;

    expect($scan->getTable())->toBe('custom_seo_scans')
        ->and($issue->getTable())->toBe('custom_seo_scan_issues')
        ->and(config('lazy-seo.tables.seo_scans'))->toBe('custom_seo_scans');
});
