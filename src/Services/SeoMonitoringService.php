<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Support\Arr;
use Step2dev\LazySeoTools\Data\CrawlResult;
use Step2dev\LazySeoTools\Models\SeoScan;
use Throwable;

class SeoMonitoringService
{
    public function __construct(
        protected SiteCrawlerService $crawler,
        protected SeoHistoryService $history,
        protected SeoAuditService $audit,
    ) {}

    public function createPendingScan(string $url, array $options = []): SeoScan
    {
        return SeoScan::query()->create([
            'start_url' => $url,
            'status' => 'pending',
            'score' => 0,
            'score_delta' => 0,
            'summary' => [],
            'regressions' => [],
            'resolved_issues' => [],
            'options' => $options,
        ]);
    }

    public function scan(string $url, array $options = []): SeoScan
    {
        return $this->runScan($this->createPendingScan($url, $options));
    }

    public function runScan(SeoScan $scan): SeoScan
    {
        $scan->markRunning();

        try {
            $result = $this->crawler->crawl($scan->start_url, $scan->options ?? []);

            return $this->store($result, $scan->options ?? [], $scan);
        } catch (Throwable $exception) {
            $scan->markFailed($exception->getMessage());

            throw $exception;
        }
    }

    public function store(CrawlResult $result, array $options = [], ?SeoScan $scan = null): SeoScan
    {
        $issues = $this->audit->issues($result);
        $previous = $this->history->latest($result->startUrl);
        $score = $this->audit->score($issues);

        $attributes = [
            'start_url' => $result->startUrl,
            'previous_scan_id' => $previous?->id,
            'status' => 'completed',
            'score' => $score,
            'score_delta' => $previous ? $score - $previous->score : 0,
            'pages_count' => count($result->pages),
            'issues_count' => count($issues),
            'broken_links_count' => count($result->brokenLinks),
            'external_broken_links_count' => count($result->externalBrokenLinks),
            'redirect_chains_count' => count($result->redirectChains),
            'duplicate_titles_count' => count($result->duplicateTitles),
            'duplicate_descriptions_count' => count($result->duplicateDescriptions),
            'canonical_conflicts_count' => count($result->canonicalConflicts),
            'summary' => Arr::except($result->toArray(), ['pages']),
            'regressions' => [],
            'resolved_issues' => [],
            'options' => $options,
            'failure_reason' => null,
            'finished_at' => now(),
        ];

        if ($scan) {
            $scan->forceFill($attributes)->save();
            $scan->issues()->delete();
        } else {
            $scan = SeoScan::query()->create($attributes);
        }

        foreach ($issues as $issue) {
            $scan->issues()->create($issue);
        }

        $regressions = $previous ? $this->history->newIssues($scan, $previous) : [];
        $resolved = $previous ? $this->history->resolvedIssues($scan, $previous) : [];

        $scan->forceFill([
            'issues_count' => count($issues),
            'new_issues_count' => count($regressions),
            'resolved_issues_count' => count($resolved),
            'regressions' => $regressions,
            'resolved_issues' => $resolved,
        ])->save();

        return $scan->load('issues', 'previousScan');
    }

    /** @return array<int, array<string, mixed>> */
    public function issuesFromResult(CrawlResult $result): array
    {
        return $this->audit->issues($result);
    }
}
