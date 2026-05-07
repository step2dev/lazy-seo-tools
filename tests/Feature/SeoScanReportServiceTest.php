<?php

use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Services\SeoScanReportService;

it('builds a compact scan report with issue breakdowns', function (): void {
    $scan = SeoScan::query()->create([
        'start_url' => 'https://example.com',
        'status' => 'completed',
        'score' => 68,
        'score_delta' => -7,
        'pages_count' => 4,
        'issues_count' => 2,
        'new_issues_count' => 1,
        'resolved_issues_count' => 1,
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

    $scan->issues()->create([
        'url' => 'https://example.com/about',
        'type' => 'missing_description',
        'severity' => 'warning',
        'status' => 'resolved',
        'message' => 'Missing description.',
        'fingerprint' => sha1('missing_description'),
    ]);

    $report = app(SeoScanReportService::class)->make($scan);

    expect($report['scan_id'])->toBe($scan->id);
    expect($report['passed'])->toBeFalse();
    expect($report['severity_breakdown'])->toHaveCount(2);
    expect($report['type_breakdown'])->toHaveCount(1);
    expect($report['top_issues'])->toHaveCount(1);
    expect($report['top_issues'][0]['type'])->toBe('missing_title');
});
