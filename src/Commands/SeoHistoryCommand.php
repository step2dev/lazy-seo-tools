<?php

namespace Step2dev\LazySeoTools\Commands;

use Illuminate\Console\Command;
use Step2dev\LazySeoTools\Concerns\EnsuresFeatureIsEnabled;
use Step2dev\LazySeoTools\Services\SeoHistoryService;

class SeoHistoryCommand extends Command
{
    use EnsuresFeatureIsEnabled;

    public $signature = 'lazy-seo:history
        {url? : Optional URL/domain to filter scan history}
        {--limit=10 : Number of recent scans}
        {--json : Output JSON}';

    public $description = 'Show SEO score history, issue trends and regressions.';

    public function handle(SeoHistoryService $history): int
    {
        if (! $this->ensureFeatureIsEnabled('monitoring')) {
            return self::FAILURE;
        }

        $summary = $history->summarize(
            startUrl: $this->argument('url') ?: null,
            limit: (int) $this->option('limit'),
        );

        if ($this->option('json')) {
            $this->line(json_encode($summary->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $this->components->info('SEO history summary');
        $this->line('Current score: '.($summary->currentScore ?? 'n/a'));
        $this->line('Previous score: '.($summary->previousScore ?? 'n/a'));
        $this->line('Score delta: '.$summary->scoreDelta);
        $this->line('Regressions: '.count($summary->regressions));
        $this->line('Resolved issues: '.count($summary->resolved));

        return self::SUCCESS;
    }
}
