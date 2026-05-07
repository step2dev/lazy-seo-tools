<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Support\Collection;
use Step2dev\LazySeoTools\Data\SeoHistorySummary;
use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Models\SeoScanIssue;

class SeoHistoryService
{
    public function summarize(?string $startUrl = null, int $limit = 10): SeoHistorySummary
    {
        $scans = $this->scans($startUrl, $limit);
        $current = $scans->first();
        $previous = $scans->skip(1)->first();

        $regressions = $current instanceof SeoScan && $previous instanceof SeoScan
            ? $this->newIssues($current, $previous)
            : [];

        $resolved = $current instanceof SeoScan && $previous instanceof SeoScan
            ? $this->resolvedIssues($current, $previous)
            : [];

        return new SeoHistorySummary(
            currentScore: $current?->score,
            previousScore: $previous?->score,
            scoreDelta: ($current?->score ?? 0) - ($previous?->score ?? 0),
            scoreTrend: $scans->reverse()->map(fn (SeoScan $scan): array => [
                'id' => $scan->id,
                'score' => $scan->score,
                'pages_count' => $scan->pages_count,
                'issues_count' => $scan->issues_count,
                'created_at' => $scan->created_at?->toISOString(),
            ])->values()->all(),
            issueTrend: $this->issueTrend($scans),
            regressions: $regressions,
            resolved: $resolved,
        );
    }

    public function latest(?string $startUrl = null): ?SeoScan
    {
        return $this->baseQuery($startUrl)->latest('created_at')->first();
    }

    public function previous(SeoScan $scan): ?SeoScan
    {
        return SeoScan::query()
            ->where('start_url', $scan->start_url)
            ->where('id', '<>', $scan->id)
            ->where('created_at', '<=', $scan->created_at)
            ->latest('created_at')
            ->first();
    }

    /** @return array<int, array<string, mixed>> */
    public function newIssues(SeoScan $current, SeoScan $previous): array
    {
        $previousKeys = $this->issueKeys($previous);

        return $current->issues()
            ->get()
            ->reject(fn (SeoScanIssue $issue): bool => in_array($this->issueKey($issue), $previousKeys, true))
            ->map(fn (SeoScanIssue $issue): array => $issue->only(['url', 'type', 'severity', 'message', 'context']))
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function resolvedIssues(SeoScan $current, SeoScan $previous): array
    {
        $currentKeys = $this->issueKeys($current);

        return $previous->issues()
            ->get()
            ->reject(fn (SeoScanIssue $issue): bool => in_array($this->issueKey($issue), $currentKeys, true))
            ->map(fn (SeoScanIssue $issue): array => $issue->only(['url', 'type', 'severity', 'message', 'context']))
            ->values()
            ->all();
    }

    /** @return Collection<int, SeoScan> */
    protected function scans(?string $startUrl, int $limit): Collection
    {
        return $this->baseQuery($startUrl)
            ->latest('created_at')
            ->limit(max(1, $limit))
            ->get();
    }

    protected function baseQuery(?string $startUrl)
    {
        return SeoScan::query()
            ->when($startUrl, fn ($query) => $query->where('start_url', $startUrl));
    }

    /** @param Collection<int, SeoScan> $scans */
    protected function issueTrend(Collection $scans): array
    {
        return $scans
            ->reverse()
            ->mapWithKeys(fn (SeoScan $scan): array => [
                (string) $scan->id => [
                    'errors' => $scan->issues()->where('severity', 'error')->count(),
                    'warnings' => $scan->issues()->where('severity', 'warning')->count(),
                    'notices' => $scan->issues()->where('severity', 'notice')->count(),
                ],
            ])
            ->all();
    }

    /** @return array<int, string> */
    protected function issueKeys(SeoScan $scan): array
    {
        return $scan->issues()->get()->map(fn (SeoScanIssue $issue): string => $this->issueKey($issue))->all();
    }

    protected function issueKey(SeoScanIssue $issue): string
    {
        return implode('|', [
            (string) $issue->url,
            (string) $issue->type,
            (string) $issue->severity,
            (string) $issue->message,
        ]);
    }
}
