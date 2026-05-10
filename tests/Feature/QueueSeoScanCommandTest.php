<?php

use Illuminate\Support\Facades\Bus;
use Step2dev\LazySeoTools\Jobs\RunSeoScanJob;
use Step2dev\LazySeoTools\Models\SeoScan;

it('dispatches queued seo scan job', function (): void {
    config()->set('lazy-seo.features.crawler', true);
    config()->set('lazy-seo.features.monitoring', true);
    Bus::fake();

    $this->artisan('lazy-seo:crawl-queue', [
        'url' => 'https://example.com/',
        '--max-pages' => 5,
    ])->assertExitCode(0);

    Bus::assertDispatched(RunSeoScanJob::class, function (RunSeoScanJob $job): bool {
        $scan = SeoScan::query()->find($job->scanId);

        return $scan?->start_url === 'https://example.com/'
            && ($scan->options['max_pages'] ?? null) === 5;
    });
});
