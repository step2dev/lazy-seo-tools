<?php

namespace Step2dev\LazySeoTools\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Services\SeoMonitoringService;
use Throwable;

class RunSeoScanJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public int $timeout = 600;

    public function __construct(
        public readonly int $scanId,
    ) {
        $this->tries = (int) config('lazy-seo.queue.tries', 2);
        $this->timeout = (int) config('lazy-seo.queue.timeout', 600);

        $connection = config('lazy-seo.queue.connection');

        if (is_string($connection) && $connection !== '') {
            $this->onConnection($connection);
        }

        $this->onQueue((string) config('lazy-seo.queue.queue', 'default'));
    }

    public function handle(SeoMonitoringService $monitoring): void
    {
        $scan = SeoScan::query()->findOrFail($this->scanId);

        $monitoring->runScan($scan);
    }

    public function failed(Throwable $exception): void
    {
        $scan = SeoScan::query()->find($this->scanId);

        $scan?->markFailed($exception->getMessage());
    }
}
