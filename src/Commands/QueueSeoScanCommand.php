<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Jobs\RunSeoScanJob;
use Step2dev\LazySeoTools\Services\SeoMonitoringService;

class QueueSeoScanCommand extends Command
{
    public $signature = 'lazy-seo:crawl-queue
        {url : Start URL}
        {--max-pages= : Maximum pages to crawl}
        {--check-external : Check external links with HEAD/GET requests}
        {--max-external-links= : Maximum external links to check}
        {--connection= : Queue connection}
        {--queue= : Queue name}';

    public $description = 'Create a pending SEO scan and dispatch it to the queue.';

    public function handle(SeoMonitoringService $monitoring): int
    {
        $options = array_filter([
            'max_pages' => $this->option('max-pages') ? (int) $this->option('max-pages') : null,
            'check_external_links' => $this->option('check-external') ? true : null,
            'max_external_links' => $this->option('max-external-links') ? (int) $this->option('max-external-links') : null,
            'queued' => true,
        ], static fn ($value): bool => $value !== null);

        $scan = $monitoring->createPendingScan((string) $this->argument('url'), $options);
        $job = new RunSeoScanJob((int) $scan->id);

        if ($this->option('connection')) {
            $job->onConnection((string) $this->option('connection'));
        }

        if ($this->option('queue')) {
            $job->onQueue((string) $this->option('queue'));
        }

        dispatch($job);

        $this->components->info('SEO crawl job dispatched.');
        $this->line('Scan ID: '.$scan->id);
        $this->line('Status: '.$scan->status);

        return self::SUCCESS;
    }
}
