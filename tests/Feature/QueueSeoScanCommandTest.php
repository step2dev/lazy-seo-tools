<?php

use Illuminate\Support\Facades\Bus;
use Step2dev\LazySeoTools\Jobs\RunSeoScanJob;

it('dispatches queued seo scan job', function (): void {
    Bus::fake();

    $this->artisan('lazy-seo:crawl-queue', [
        'url' => 'https://example.com/',
        '--max-pages' => 5,
    ])->assertExitCode(0);

    Bus::assertDispatched(RunSeoScanJob::class, function (RunSeoScanJob $job): bool {
        return $job->url === 'https://example.com/'
            && ($job->options['max_pages'] ?? null) === 5;
    });
});
