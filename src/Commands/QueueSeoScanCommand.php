<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Jobs\RunSeoScanJob;

class QueueSeoScanCommand extends Command
{
    public $signature = 'lazy-seo:crawl-queue
        {url : Start URL}
        {--max-pages= : Maximum pages to crawl}
        {--connection= : Queue connection}
        {--queue= : Queue name}';

    public $description = 'Dispatch an async SEO crawl and monitoring scan job.';

    public function handle(): int
    {
        $options = array_filter([
            'max_pages' => $this->option('max-pages') ? (int) $this->option('max-pages') : null,
        ], static fn ($value): bool => $value !== null);

        $job = new RunSeoScanJob((string) $this->argument('url'), $options);

        if ($this->option('connection')) {
            $job->onConnection((string) $this->option('connection'));
        }

        if ($this->option('queue')) {
            $job->onQueue((string) $this->option('queue'));
        }

        dispatch($job);

        $this->components->info('SEO crawl job dispatched.');

        return self::SUCCESS;
    }
}
