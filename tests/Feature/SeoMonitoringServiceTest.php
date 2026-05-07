<?php

use Step2dev\LazySeoTools\Data\CrawledPage;
use Step2dev\LazySeoTools\Data\CrawlResult;
use Step2dev\LazySeoTools\Data\SeoAnalysisResult;
use Step2dev\LazySeoTools\Services\SeoMonitoringService;

it('stores scan issues with fingerprints and detects resolved issues', function (): void {
    $service = app(SeoMonitoringService::class);

    $first = $service->store(new CrawlResult(
        startUrl: 'https://example.com',
        pages: [new CrawledPage(
            url: 'https://example.com',
            status: 200,
            title: null,
            description: null,
            canonical: null,
            analysis: new SeoAnalysisResult(score: 40, metrics: ['h1_count' => 0]),
        )],
    ));

    $second = $service->store(new CrawlResult(
        startUrl: 'https://example.com',
        pages: [new CrawledPage(
            url: 'https://example.com',
            status: 200,
            title: 'A production ready SEO title for Laravel',
            description: 'This is a production ready meta description that is long enough for the package audit checks.',
            canonical: 'https://example.com',
            headings: [['level' => 1, 'text' => 'Home']],
            analysis: new SeoAnalysisResult(score: 100, metrics: ['h1_count' => 1]),
        )],
    ));

    expect($first->issues)->not->toBeEmpty();
    expect($first->issues->first()->fingerprint)->toBeString()->toHaveLength(40);
    expect($second->resolved_issues_count)->toBeGreaterThan(0);
    expect($second->score)->toBeGreaterThan($first->score);
});
