<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Step2dev\LazySeoTools\Services\SiteCrawlerService;

class CrawlSiteCommand extends Command
{
    public $signature = 'lazy-seo:crawl
        {url : Start URL}
        {--max-pages= : Maximum pages to crawl}
        {--check-external : Check external links with HEAD/GET requests}
        {--max-external-links= : Maximum external links to check}
        {--output= : Optional JSON report path}';

    public $description = 'Crawl a site and generate an SEO scan report.';

    public function handle(SiteCrawlerService $crawler): int
    {
        $result = $crawler->crawl($this->argument('url'), array_filter([
            'max_pages' => $this->option('max-pages') ? (int) $this->option('max-pages') : null,
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
