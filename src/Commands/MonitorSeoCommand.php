<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Services\SeoMonitoringService;

class MonitorSeoCommand extends Command
{
    public $signature = 'lazy-seo:monitor
        {url? : URL to scan. Defaults to lazy-seo.monitoring.url or app.url}
        {--max-pages= : Maximum pages to crawl}
        {--fail-under= : Return failure when score is lower than this value}';

    public $description = 'Run SEO crawl, store monitoring snapshot and save issues to database.';

    public function handle(SeoMonitoringService $monitoring): int
    {
        $url = $this->argument('url') ?: config('lazy-seo.monitoring.url') ?: config('app.url');

        if (! is_string($url) || trim($url) === '') {
            $this->components->error('Monitoring URL is missing. Pass URL or set lazy-seo.monitoring.url.');

            return self::FAILURE;
        }

        $scan = $monitoring->scan($url, array_filter([
            'max_pages' => $this->option('max-pages') ? (int) $this->option('max-pages') : null,
        ], static fn ($value): bool => $value !== null));

        $this->components->info('SEO monitoring scan saved.');
        $this->line('Scan ID: '.$scan->id);
        $this->line('Score: '.$scan->score.'/100');
        $this->line('Pages: '.$scan->pages_count);
        $this->line('Issues: '.$scan->issues_count);

        $failUnder = $this->option('fail-under') !== null
            ? (int) $this->option('fail-under')
            : (int) config('lazy-seo.monitoring.fail_under', 75);

        return $scan->score >= $failUnder ? self::SUCCESS : self::FAILURE;
    }
}
