<?php

use Step2dev\LazySeoTools\Data\SeoData;
use Step2dev\LazySeoTools\Services\SeoAnalyzerService;

it('returns seo analyzer score with grouped issues and metrics', function (): void {
    $result = app(SeoAnalyzerService::class)->analyzePage([
        'title' => 'Short',
        'description' => '',
        'robots' => ['index', 'follow'],
        'html' => '<h1>Hello</h1><img src="/image.jpg"><p>Small content</p>',
    ]);

    expect($result->score)->toBeLessThan(100)
        ->and($result->errors)->toContain('Missing meta description.')
        ->and($result->warnings)->toContain('Title is too short.')
        ->and($result->metrics['images_missing_alt'])->toBe(1)
        ->and($result->toArray())->toHaveKeys(['score', 'grade', 'passed', 'errors', 'warnings', 'notices', 'metrics']);
});

it('analyzes seo data objects', function (): void {
    $data = SeoData::fromArray([
        'title' => 'A complete Laravel SEO package for production websites',
        'description' => 'A practical package that helps Laravel applications manage metadata, redirects, sitemaps and structured data cleanly.',
        'canonical_url' => 'https://example.com/package',
        'robots' => ['index', 'follow'],
        'image' => 'https://example.com/og.jpg',
    ]);

    $result = app(SeoAnalyzerService::class)->analyzeData($data, '<h1>Laravel SEO</h1><h2>Tools</h2><script type="application/ld+json">{}</script>'.str_repeat(' content', 260));

    expect($result->score)->toBeGreaterThanOrEqual(75)
        ->and($result->passed())->toBeTrue();
});
