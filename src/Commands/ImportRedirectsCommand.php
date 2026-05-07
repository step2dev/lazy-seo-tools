<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Services\RedirectImportExportService;

class ImportRedirectsCommand extends Command
{
    public $signature = 'lazy-seo:redirects-import {path : CSV path} {--no-update : Do not update existing redirects}';

    public $description = 'Import SEO redirects from CSV.';

    public function handle(RedirectImportExportService $service): int
    {
        $result = $service->importCsv($this->argument('path'), ! $this->option('no-update'));

        $this->components->info("Redirects imported. Created: {$result['created']}, updated: {$result['updated']}, skipped: {$result['skipped']}.");

        return self::SUCCESS;
    }
}
