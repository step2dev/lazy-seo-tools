<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Services\RedirectImportExportService;

class ExportRedirectsCommand extends Command
{
    public $signature = 'lazy-seo:redirects-export {path : CSV path}';

    public $description = 'Export SEO redirects to CSV.';

    public function handle(RedirectImportExportService $service): int
    {
        $path = $service->exportCsv($this->argument('path'));

        $this->components->info("Redirects exported: {$path}");

        return self::SUCCESS;
    }
}
