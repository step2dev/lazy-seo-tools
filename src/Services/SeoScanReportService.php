<?php

namespace Step2dev\LazySeoTools\Services;

use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Models\SeoScanIssue;

class SeoScanReportService
{
    /** @return array<string, mixed> */
    public function make(SeoScan $scan): array
    {
        $scan->loadMissing('previousScan');

        return [
            'scan_id' => $scan->id,
            'start_url' => $scan->start_url,
            'status' => $scan->status,
            'score' => $scan->score,
            'score_delta' => $scan->score_delta,
            'passed' => $scan->passed(),
            'pages_count' => $scan->pages_count,
            'issues_count' => $scan->issues_count,
            'new_issues_count' => $scan->new_issues_count,
            'resolved_issues_count' => $scan->resolved_issues_count,
            'broken_links_count' => $scan->broken_links_count,
            'external_broken_links_count' => $scan->external_broken_links_count,
            'redirect_chains_count' => $scan->redirect_chains_count,
            'duplicate_titles_count' => $scan->duplicate_titles_count,
            'duplicate_descriptions_count' => $scan->duplicate_descriptions_count,
            'canonical_conflicts_count' => $scan->canonical_conflicts_count,
            'severity_breakdown' => $this->severityBreakdown($scan),
            'type_breakdown' => $this->typeBreakdown($scan),
            'top_issues' => $this->topIssues($scan),
            'regressions' => $scan->regressions ?? [],
            'resolved_issues' => $scan->resolved_issues ?? [],
            'previous_scan_id' => $scan->previous_scan_id,
            'failure_reason' => $scan->failure_reason,
            'started_at' => $scan->started_at?->toISOString(),
            'finished_at' => $scan->finished_at?->toISOString(),
        ];
    }

    /** @return array<int, array{severity: string, status: string, count: int}> */
    protected function severityBreakdown(SeoScan $scan): array
    {
        return $scan->issues()
            ->selectRaw('severity, status, count(*) as aggregate')
            ->groupBy('severity', 'status')
            ->orderBy('severity')
            ->get()
            ->map(fn (SeoScanIssue $row): array => [
                'severity' => (string) $row->severity,
                'status' => (string) $row->status,
                'count' => (int) $row->getAttribute('aggregate'),
            ])
            ->all();
    }

    /** @return array<int, array{type: string, severity: string, count: int}> */
    protected function typeBreakdown(SeoScan $scan): array
    {
        return $scan->issues()
            ->where('status', 'open')
            ->selectRaw('type, severity, count(*) as aggregate')
            ->groupBy('type', 'severity')
            ->orderByDesc('aggregate')
            ->limit(20)
            ->get()
            ->map(fn (SeoScanIssue $row): array => [
                'type' => (string) $row->type,
                'severity' => (string) $row->severity,
                'count' => (int) $row->getAttribute('aggregate'),
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    protected function topIssues(SeoScan $scan): array
    {
        $limit = max(1, (int) config('lazy-seo.alerts.include_issues_limit', 10));

        return $scan->issues()
            ->where('status', 'open')
            ->orderByRaw("case severity when 'error' then 0 when 'warning' then 1 else 2 end")
            ->orderBy('type')
            ->limit($limit)
            ->get()
            ->map(fn (SeoScanIssue $issue): array => [
                'url' => $issue->url,
                'type' => $issue->type,
                'severity' => $issue->severity,
                'message' => $issue->message,
                'fingerprint' => $issue->fingerprint,
                'context' => $issue->context ?? [],
            ])
            ->all();
    }
}
