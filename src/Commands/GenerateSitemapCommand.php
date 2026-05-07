<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Services\SitemapGeneratorService;

class GenerateSitemapCommand extends Command
{
    public $signature = 'lazy-seo:sitemap
        {--path= : Relative path inside public directory}
        {--cached : Return the sitemap path from cache or generate it when missing}
        {--warm : Generate sitemap files and warm the configured cache key}
        {--clear-cache : Clear the configured sitemap cache before generating}
        {--json : Output generated file paths as JSON}';

    public $description = 'Generate sitemap.xml, sitemap indexes and chunked sitemap files from lazy SEO records and configured model sources.';

    public function handle(SitemapGeneratorService $sitemap): int
    {
        if ($this->option('clear-cache')) {
            $sitemap->clearCache();
        }

        if ($this->option('warm')) {
            $result = $sitemap->warmCache(path: $this->option('path'));
        } elseif ($this->option('cached')) {
            $path = $sitemap->cached(path: $this->option('path'));
            $result = ['files' => [$path], 'cached_path' => $path];
        } else {
            $result = $sitemap->generateFiles(path: $this->option('path'));
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        if (isset($result['index'])) {
            $this->components->info("Sitemap index generated: {$result['index']}");
        }

        foreach ($result['files'] as $file) {
            $this->components->info("Sitemap generated: {$file}");
        }

        if (isset($result['cached_path'])) {
            $this->components->info("Sitemap cache warmed: {$result['cached_path']}");
        }

        return self::SUCCESS;
    }
}
