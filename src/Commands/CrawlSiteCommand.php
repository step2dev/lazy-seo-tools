<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Step2dev\LazySeoTools\Concerns\EnsuresFeatureIsEnabled;
use Step2dev\LazySeoTools\Services\SiteCrawlerService;

class CrawlSiteCommand extends Command
{
    use EnsuresFeatureIsEnabled;

    public $signature = 'lazy-seo:crawl
        {url : Start URL}
        {--max-pages= : Maximum pages to crawl}
        {--max-depth= : Maximum internal link depth}
        {--rate-limit-ms= : Delay between crawler HTTP requests in milliseconds}
        {--check-external : Check external links with HEAD/GET requests}
        {--max-external-links= : Maximum external links to check}
        {--output= : Optional JSON report path}';

    public $description = 'Crawl a site and generate an SEO scan report.';

    public function handle(SiteCrawlerService $crawler): int
    {
        if (! $this->ensureFeatureIsEnabled('crawler')) {
            return self::FAILURE;
        }

        if ((bool) config('lazy-seo.crawler.queue_only', false)) {
            $this->components->error('Synchronous crawling is disabled by lazy-seo.crawler.queue_only. Use lazy-seo:crawl-queue or lazy-seo:monitor --queue.');

            return self::FAILURE;
        }

        $result = $crawler->crawl($this->argument('url'), array_filter([
            'max_pages' => $this->option('max-pages') ? (int) $this->option('max-pages') : null,
            'max_depth' => $this->option('max-depth') ? (int) $this->option('max-depth') : null,
            'rate_limit_ms' => $this->option('rate-limit-ms') !== null ? (int) $this->option('rate-limit-ms') : null,
            'check_external_links' => $this->option('check-external') ? true : null,
            'max_external_links' => $this->option('max-external-links') ? (int) $this->option('max-external-links') : null,
        ], static fn ($value): bool => $value !== null));

        $this->components->info('SEO crawl finished.');
        $this->line('Score: '.$result->score().'/100');
        $this->line('Pages: '.count($result->pages));
        $this->line('Broken internal links: '.count($result->brokenLinks));
        $this->line('Broken external links: '.count($result->externalBrokenLinks));
        $this->line('Redirect chains: '.count($result->redirectChains));
        $this->line('Duplicate titles: '.count($result->duplicateTitles));

        if ($this->option('output')) {
            $path = base_path($this->option('output'));
            File::ensureDirectoryExists(dirname($path));
            File::put($path, json_encode($result->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            $this->components->info('Report saved: '.$path);
        }

        return $result->score() >= 75 ? self::SUCCESS : self::FAILURE;
    }
}
