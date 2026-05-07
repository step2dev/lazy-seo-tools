<?php

use Illuminate\Support\Carbon;
use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Services\SeoHistoryService;

it('summarizes score history regressions and resolved issues', function (): void {
    Carbon::setTestNow(now()->subMinutes(10));

    $previous = SeoScan::query()->create([
        'start_url' => 'https://example.com/',
        'score' => 90,
        'pages_count' => 1,
        'issues_count' => 1,
        'finished_at' => now(),
    ]);

    $previous->issues()->create([
        'url' => 'https://example.com/old',
        'type' => 'missing_title',
        'severity' => 'error',
        'message' => 'Old issue.',
        'context' => [],
    ]);

    Carbon::setTestNow(now()->addMinutes(10));

    $current = SeoScan::query()->create([
        'start_url' => 'https://example.com/',
        'previous_scan_id' => $previous->id,
        'score' => 80,
        'score_delta' => -10,
        'pages_count' => 1,
        'issues_count' => 1,
        'finished_at' => now(),
    ]);

    $current->issues()->create([
        'url' => 'https://example.com/new',
        'type' => 'broken_link',
        'severity' => 'error',
        'message' => 'New issue.',
        'context' => [],
    ]);

    $summary = app(SeoHistoryService::class)->summarize('https://example.com/', 2);

    expect($summary->currentScore)->toBe(80)
        ->and($summary->previousScore)->toBe(90)
        ->and($summary->scoreDelta)->toBe(-10)
        ->and($summary->regressions)->toHaveCount(1)
        ->and($summary->resolved)->toHaveCount(1)
        ->and($summary->scoreTrend)->toHaveCount(2);
});
