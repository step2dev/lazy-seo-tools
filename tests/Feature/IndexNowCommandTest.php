<?php

use Illuminate\Support\Facades\Http;
use Step2dev\LazySeoTools\Models\SeoIndexingLog;

it('runs indexnow command', function (): void {
    config()->set('lazy-seo.features.indexnow', true);
    config()->set('lazy-seo.indexnow.key', 'command-key');
    Http::fake(['api.indexnow.org/*' => Http::response('', 202)]);

    $this->artisan('lazy-seo:indexnow https://example.com/page')
        ->assertExitCode(0);

    expect(SeoIndexingLog::query()->count())->toBe(1);
});
