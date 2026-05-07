<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Services\SitemapGeneratorService;

class WarmSitemapCommand extends Command
{
    public $signature = 'lazy-seo:sitemap:warm
        {--path= : Relative path inside public directory}
        {--clear : Clear sitemap cache before warming}';

    public $description = 'Generate sitemap files and warm the configured sitemap cache.';

    public function handle(SitemapGeneratorService $sitemap): int
    {
        if ($this->option('clear')) {
            $sitemap->clearCache();
        }

        $result = $sitemap->warmCache(path: $this->option('path'));

        if (isset($result['index'])) {
            $this->components->info("Sitemap index generated: {$result['index']}");
        }

        foreach ($result['files'] as $file) {
            $this->components->info("Sitemap generated: {$file}");
        }

        $this->components->info("Sitemap cache warmed: {$result['cached_path']}");

        return self::SUCCESS;
    }
}
