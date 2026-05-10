<?php

use Illuminate\Support\Facades\Http;
use Step2dev\LazySeoTools\Models\SeoScan;

it('runs monitoring command and stores scan', function (): void {
    config()->set('lazy-seo.features.crawler', true);
    config()->set('lazy-seo.features.monitoring', true);
    Http::fake([
        'https://example.com/' => Http::response('<html><head><title>Command SEO Title</title><meta name="description" content="Valid command description for seo monitoring."><link rel="canonical" href="https://example.com/"></head><body><h1>Home</h1>'.str_repeat(' content', 260).'</body></html>', 200),
    ]);

    $this->artisan('lazy-seo:monitor', ['url' => 'https://example.com/', '--max-pages' => 1, '--fail-under' => 1])
        ->assertExitCode(0);

    expect(SeoScan::query()->count())->toBe(1);
});
