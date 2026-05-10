<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Concerns\EnsuresFeatureIsEnabled;
use Step2dev\LazySeoTools\Services\IndexNowService;

class IndexNowCommand extends Command
{
    use EnsuresFeatureIsEnabled;

    public $signature = 'lazy-seo:indexnow
        {urls?* : Absolute URLs to submit}
        {--sitemap : Submit configured sitemap URL instead of explicit URLs}
        {--file= : Read URLs from file, one URL per line}
        {--key= : Override IndexNow key}
        {--endpoint= : Override IndexNow endpoint}
        {--no-log : Do not store indexing log}';

    public $description = 'Submit URLs to IndexNow.';

    public function handle(IndexNowService $indexNow): int
    {
        if (! $this->ensureFeatureIsEnabled('indexnow')) {
            return self::FAILURE;
        }

        $urls = $this->argument('urls') ?: [];

        if ($this->option('file')) {
            $file = (string) $this->option('file');

            if (! is_file($file)) {
                $this->components->error('URL file does not exist: '.$file);

                return self::FAILURE;
            }

            $urls = array_merge($urls, file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []);
        }

        if ($this->option('sitemap')) {
            $urls[] = url('/'.ltrim((string) config('lazy-seo.sitemap.index_path', config('lazy-seo.sitemap.path', 'sitemap.xml')), '/'));
        }

        $result = $indexNow->submit($urls, array_filter([
            'key' => $this->option('key'),
            'endpoint' => $this->option('endpoint'),
            'log' => ! $this->option('no-log'),
        ], static fn (mixed $value): bool => $value !== null));

        if (! ($result['successful'] ?? false)) {
            $this->components->error($result['message'] ?? 'IndexNow request failed.');

            return self::FAILURE;
        }

        $this->components->info('IndexNow submission completed.');
        $this->line('URLs: '.count($result['urls'] ?? []));

        return self::SUCCESS;
    }
}
