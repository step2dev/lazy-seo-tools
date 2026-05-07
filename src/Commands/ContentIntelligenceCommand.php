<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Services\ContentIntelligenceService;

class ContentIntelligenceCommand extends Command
{
    public $signature = 'lazy-seo:content
        {file : HTML file to analyze}
        {--keywords= : Comma-separated target keywords}
        {--base-url= : Base URL for internal/external link classification}
        {--json : Output JSON}';

    public $description = 'Analyze content structure, readability, keywords, images and internal links.';

    public function handle(ContentIntelligenceService $service): int
    {
        $file = (string) $this->argument('file');

        if (! is_file($file)) {
            $this->components->error('File does not exist: '.$file);

            return self::FAILURE;
        }

        $result = $service->analyze(
            html: (string) file_get_contents($file),
            targetKeywords: (string) ($this->option('keywords') ?? ''),
            baseUrl: $this->option('base-url') ? (string) $this->option('base-url') : null,
        );

        if ($this->option('json')) {
            $this->line(json_encode($result->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $result->passed() ? self::SUCCESS : self::FAILURE;
        }

        $this->line('Score: '.$result->score.'/100');

        foreach ($result->warnings as $warning) {
            $this->warn($warning);
        }

        foreach ($result->suggestions as $suggestion) {
            $this->line('- '.$suggestion);
        }

        return $result->passed() ? self::SUCCESS : self::FAILURE;
    }
}
