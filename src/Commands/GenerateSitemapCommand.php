<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Services\SitemapGeneratorService;

class GenerateSitemapCommand extends Command
{
    public $signature = 'lazy-seo:sitemap {--path= : Relative path inside public directory}';

    public $description = 'Generate sitemap.xml from lazy SEO records.';

    public function handle(SitemapGeneratorService $sitemap): int
    {
        $path = $sitemap->generate(path: $this->option('path'));

        $this->components->info("Sitemap generated: {$path}");

        return self::SUCCESS;
    }
}
