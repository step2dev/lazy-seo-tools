<?php

use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Services\SeoDashboardService;

it('builds dashboard overview and scan detail metrics', function (): void {
    $scan = SeoScan::query()->create([
        'start_url' => 'https://example.com',
        'score' => 72,
        'score_delta' => -5,
        'pages_count' => 3,
        'issues_count' => 2,
        'broken_links_count' => 1,
        'external_broken_links_count' => 1,
        'summary' => [
            'broken_links' => ['https://example.com/missing'],
            'external_broken_links' => ['https://external.test/missing'],
            'redirect_chains' => [],
        ],
        'finished_at' => now(),
    ]);

    $scan->issues()->create([
        'url' => 'https://example.com',
        'type' => 'missing_title',
        'severity' => 'error',
        'status' => 'open',
        'message' => 'Missing title',
        'fingerprint' => sha1('missing_title'),
    ]);

    $scan->issues()->create([
        'url' => 'https://example.com/about',
        'type' => 'missing_description',
        'severity' => 'warning',
        'status' => 'ignored',
        'message' => 'Missing description',
        'fingerprint' => sha1('missing_description'),
    ]);

    $service = app(SeoDashboardService::class);
    $overview = $service->overview();
    $detail = $service->scanDetail($scan);

    expect($overview['latest']->is($scan))->toBeTrue();
    expect($overview['criticalIssues'])->toBe(1);
    expect($overview['ignoredIssues'])->toBe(1);
    expect($overview['worstPages'])->toHaveCount(1);
    expect($detail['openIssues'])->toBe(1);
    expect($detail['ignoredIssues'])->toBe(1);
    expect($detail['links']['broken'])->toBe(['https://example.com/missing']);
});
