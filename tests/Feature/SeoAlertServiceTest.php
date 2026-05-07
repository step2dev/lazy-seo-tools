<?php

use Illuminate\Support\Facades\Http;
use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Services\SeoAlertService;

it('does not alert when alerts are disabled', function (): void {
    config()->set('lazy-seo.alerts.enabled', false);
    Http::fake();

    $scan = SeoScan::query()->create([
        'start_url' => 'https://example.com',
        'status' => 'completed',
        'score' => 20,
        'finished_at' => now(),
    ]);

    expect(app(SeoAlertService::class)->notifyIfNeeded($scan))->toBeFalse();
    Http::assertNothingSent();
});

it('sends alert webhook for low score and respects cooldown', function (): void {
    config()->set('lazy-seo.alerts.enabled', true);
    config()->set('lazy-seo.alerts.webhook_url', 'https://alerts.test/seo');
    config()->set('lazy-seo.alerts.score_threshold', 75);
    config()->set('lazy-seo.alerts.critical_issues_threshold', 1);
    config()->set('lazy-seo.alerts.new_issues_threshold', 1);
    config()->set('lazy-seo.alerts.cooldown_minutes', 60);

    Http::fake([
        'alerts.test/*' => Http::response(['ok' => true]),
    ]);

    $scan = SeoScan::query()->create([
        'start_url' => 'https://example.com',
        'status' => 'completed',
        'score' => 50,
        'new_issues_count' => 2,
        'finished_at' => now(),
    ]);

    $scan->issues()->create([
        'url' => 'https://example.com',
        'type' => 'missing_title',
        'severity' => 'error',
        'status' => 'open',
        'message' => 'Missing title.',
        'fingerprint' => sha1('missing_title'),
    ]);

    $service = app(SeoAlertService::class);

    expect($service->reasonsFor($scan))->toContain('score_below_threshold', 'critical_issues_threshold', 'new_issues_threshold');
    expect($service->notifyIfNeeded($scan))->toBeTrue();
    expect($service->notifyIfNeeded($scan))->toBeFalse();

    Http::assertSentCount(1);
    Http::assertSent(fn ($request): bool => $request->url() === 'https://alerts.test/seo'
        && $request['event'] === 'lazy_seo.scan_alert'
        && $request['report']['scan_id'] === $scan->id);
});

it('can alert for failed scans', function (): void {
    config()->set('lazy-seo.alerts.enabled', true);
    config()->set('lazy-seo.alerts.webhook_url', 'https://alerts.test/seo');
    config()->set('lazy-seo.alerts.failed_scans', true);
    config()->set('lazy-seo.alerts.cooldown_minutes', 0);

    Http::fake([
        'alerts.test/*' => Http::response(['ok' => true]),
    ]);

    $scan = SeoScan::query()->create([
        'start_url' => 'https://example.com',
        'status' => 'failed',
        'failure_reason' => 'Crawler timeout',
        'finished_at' => now(),
    ]);

    expect(app(SeoAlertService::class)->reasonsFor($scan))->toBe(['scan_failed']);
    expect(app(SeoAlertService::class)->notifyIfNeeded($scan))->toBeTrue();

    Http::assertSentCount(1);
});
