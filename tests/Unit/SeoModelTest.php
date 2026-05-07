<?php

use Step2dev\LazySeoTools\Models\Seo;

it('stores a safe url lookup hash instead of indexing long urls directly', function (): void {
    $seo = Seo::query()->create([
        'url' => 'https://example.com/About/?utm=test',
        'title' => ['en' => 'About'],
    ]);

    expect($seo->url_hash)->toBe(sha1('/about?utm=test'));

    $found = Seo::query()->forUrl('/about?utm=test')->first();

    expect($found?->is($seo))->toBeTrue();
});
