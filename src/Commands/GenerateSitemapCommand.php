<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Services\SitemapGeneratorService;

class GenerateSitemapCommand extends Command
{
    public $signature = 'lazy-seo:sitemap
        {--path= : Relative path inside public directory}
        {--cached : Generate through configured cache}
        {--clear-cache : Clear the configured sitemap cache before generating}';

    public $description = 'Generate sitemap.xml from lazy SEO records and configured model sources.';

    public function handle(SitemapGeneratorService $sitemap): int
    {
        if ($this->option('clear-cache')) {
            $sitemap->clearCache();
        }

        $path = $this->option('cached')
            ? $sitemap->cached()
            : $sitemap->generate(path: $this->option('path'));

        $this->components->info("Sitemap generated: {$path}");

        return self::SUCCESS;
    }
}
