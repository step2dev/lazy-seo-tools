<?php

use Step2dev\LazySeoTools\Data\CrawledPage;
use Step2dev\LazySeoTools\Data\CrawlResult;
use Step2dev\LazySeoTools\Data\SeoAnalysisResult;
use Step2dev\LazySeoTools\Services\SeoAuditService;

it('creates typed seo audit issues with stable fingerprints', function (): void {
    $page = new CrawledPage(
        url: 'https://example.com/about',
        status: 200,
        title: 'Short',
        description: null,
        canonical: null,
        headings: [],
        images: [
            ['src' => 'https://example.com/image.jpg', 'missing_alt' => true],
        ],
        analysis: new SeoAnalysisResult(score: 60, metrics: ['h1_count' => 0]),
    );

    $issues = app(SeoAuditService::class)->issues(new CrawlResult(
        startUrl: 'https://example.com',
        pages: [$page],
        brokenLinks: ['https://example.com/missing' => ['https://example.com/about']],
        externalBrokenLinks: ['https://external.test/missing' => ['status' => 404, 'sources' => ['https://example.com/about']]],
    ));

    expect(collect($issues)->pluck('type')->all())->toContain(
        'title_too_short',
        'missing_description',
        'missing_canonical',
        'missing_h1',
        'missing_image_alt',
        'broken_link',
        'broken_external_link',
    );

    foreach ($issues as $issue) {
        expect($issue['fingerprint'])->toBeString()->toHaveLength(40);
    }
});

it('scores audits by configured severity weights', function (): void {
    config()->set('lazy-seo.audit.severity_weights', [
        'error' => 10,
        'warning' => 5,
        'notice' => 1,
    ]);

    $score = app(SeoAuditService::class)->score([
        ['severity' => 'error'],
        ['severity' => 'warning'],
        ['severity' => 'notice'],
    ]);

    expect($score)->toBe(84);
});
