<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Jobs\RunSeoScanJob;
use Step2dev\LazySeoTools\Services\SeoMonitoringService;

class MonitorSeoCommand extends Command
{
    public $signature = 'lazy-seo:monitor
        {url? : URL to scan. Defaults to lazy-seo.monitoring.url or app.url}
        {--max-pages= : Maximum pages to crawl}
        {--check-external : Check external links with HEAD/GET requests}
        {--max-external-links= : Maximum external links to check}
        {--queue : Dispatch scan to queue instead of running synchronously}
        {--queue-name= : Queue name for queued scans}
        {--connection= : Queue connection for queued scans}
        {--fail-under= : Return failure when score is lower than this value}';

    public $description = 'Run SEO crawl, store monitoring snapshot and save issues to database.';

    public function handle(SeoMonitoringService $monitoring): int
    {
        $url = $this->argument('url') ?: config('lazy-seo.monitoring.url') ?: config('app.url');

        if (! is_string($url) || trim($url) === '') {
            $this->components->error('Monitoring URL is missing. Pass URL or set lazy-seo.monitoring.url.');

            return self::FAILURE;
        }

        $options = array_filter([
            'max_pages' => $this->option('max-pages') ? (int) $this->option('max-pages') : null,
            'check_external_links' => $this->option('check-external') ? true : null,
            'max_external_links' => $this->option('max-external-links') ? (int) $this->option('max-external-links') : null,
        ], static fn ($value): bool => $value !== null);

        if ($this->option('queue')) {
            $job = new RunSeoScanJob($url, $options);

            if ($this->option('connection')) {
                $job->onConnection((string) $this->option('connection'));
            }

            if ($this->option('queue-name')) {
                $job->onQueue((string) $this->option('queue-name'));
            }

            dispatch($job);
            $this->components->info('SEO monitoring scan queued.');

            return self::SUCCESS;
        }

        $scan = $monitoring->scan($url, $options);

        $this->components->info('SEO monitoring scan saved.');
        $this->line('Scan ID: '.$scan->id);
        $this->line('Score: '.$scan->score.'/100');
        $this->line('Pages: '.$scan->pages_count);
        $this->line('Issues: '.$scan->issues_count);
        $this->line('Broken internal links: '.$scan->broken_links_count);
        $this->line('Broken external links: '.$scan->external_broken_links_count);
        $this->line('Score delta: '.$scan->score_delta);
        $this->line('New issues: '.$scan->new_issues_count);
        $this->line('Resolved issues: '.$scan->resolved_issues_count);

        $failUnder = $this->option('fail-under') !== null
            ? (int) $this->option('fail-under')
            : (int) config('lazy-seo.monitoring.fail_under', 75);

        return $scan->score >= $failUnder ? self::SUCCESS : self::FAILURE;
    }
}
