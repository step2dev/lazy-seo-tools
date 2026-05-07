<?php

use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Services\SeoMonitoringService;

it('creates pending scans and tracks lifecycle statuses', function (): void {
    $scan = app(SeoMonitoringService::class)->createPendingScan('https://example.com', [
        'max_pages' => 10,
    ]);

    expect($scan->status)->toBe('pending');
    expect($scan->options)->toBe(['max_pages' => 10]);

    $scan->markRunning();
    $scan->refresh();

    expect($scan->status)->toBe('running');
    expect($scan->started_at)->not->toBeNull();

    $scan->markFailed('Crawler timeout');
    $scan->refresh();

    expect($scan->status)->toBe('failed');
    expect($scan->failure_reason)->toBe('Crawler timeout');
    expect($scan->finished_at)->not->toBeNull();
});

it('exposes scan status query scopes', function (): void {
    SeoScan::query()->create(['start_url' => 'https://example.com/a', 'status' => 'pending']);
    SeoScan::query()->create(['start_url' => 'https://example.com/b', 'status' => 'running']);
    SeoScan::query()->create(['start_url' => 'https://example.com/c', 'status' => 'completed']);
    SeoScan::query()->create(['start_url' => 'https://example.com/d', 'status' => 'failed']);

    expect(SeoScan::query()->pending()->count())->toBe(1);
    expect(SeoScan::query()->running()->count())->toBe(1);
    expect(SeoScan::query()->completed()->count())->toBe(1);
    expect(SeoScan::query()->failed()->count())->toBe(1);
});
