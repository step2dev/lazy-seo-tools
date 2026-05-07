<?php

use Step2dev\LazySeoTools\Services\ContentIntelligenceService;

it('analyzes content intelligence metrics', function (): void {
    $html = '<h1>Laravel SEO Tools</h1><h2>Features</h2><p>Laravel SEO tools help Laravel developers improve pages.</p><img src="/x.jpg"><a href="/docs">Docs</a>';

    $result = app(ContentIntelligenceService::class)->analyze($html, ['laravel']);

    expect($result->metrics['word_count'])->toBeGreaterThan(5);
    expect($result->headings['h1_count'])->toBe(1);
    expect($result->metrics['missing_alt_images'])->toBe(1);
    expect($result->keywords['laravel']['count'])->toBeGreaterThan(0);
});
