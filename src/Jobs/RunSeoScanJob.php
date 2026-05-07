<?php

namespace Step2dev\LazySeoTools\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Services\SeoMonitoringService;

class RunSeoScanJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    /** @param array<string, mixed> $options */
    public function __construct(
        public readonly string $url,
        public readonly array $options = [],
    ) {
        $this->onQueue((string) config('lazy-seo.queue.queue', 'default'));
    }

    public function handle(SeoMonitoringService $monitoring): SeoScan
    {
        return $monitoring->scan($this->url, array_merge($this->options, [
            'queued' => true,
        ]));
    }
}
