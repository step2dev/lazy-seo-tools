<?php

use Illuminate\Support\Facades\Http;
use Step2dev\LazySeoTools\Models\SeoIndexingLog;
use Step2dev\LazySeoTools\Services\IndexNowService;

it('submits urls to indexnow and stores log', function (): void {
    config()->set('lazy-seo.indexnow.key', 'test-key');
    config()->set('lazy-seo.indexnow.endpoint', 'https://api.indexnow.org/indexnow');

    Http::fake([
        'api.indexnow.org/*' => Http::response('', 202),
    ]);

    $result = app(IndexNowService::class)->submit('https://example.com/post');

    expect($result['successful'])->toBeTrue();
    expect(SeoIndexingLog::query()->count())->toBe(1);

    Http::assertSent(fn ($request): bool => $request->url() === 'https://api.indexnow.org/indexnow'
        && $request['key'] === 'test-key'
        && $request['urlList'] === ['https://example.com/post']);
});
