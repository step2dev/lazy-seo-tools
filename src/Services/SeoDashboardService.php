<?php

namespace Step2dev\LazySeoTools\Services;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Step2dev\LazySeoTools\Models\SeoRedirect;
use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Models\SeoScanIssue;

class SeoDashboardService
{
    /** @return array<string, mixed> */
    public function overview(int $limit = 10): array
    {
        $latest = SeoScan::query()->latestFirst()->first();
        $scans = SeoScan::query()->latestFirst()->limit($limit)->get();
        $scanIds = $scans->pluck('id')->all();

        return [
            'latest' => $latest,
            'scans' => $scans,
            'criticalIssues' => $this->issuesFor($latest)->where('severity', 'error')->where('status', 'open')->count(),
            'warningIssues' => $this->issuesFor($latest)->where('severity', 'warning')->where('status', 'open')->count(),
            'noticeIssues' => $this->issuesFor($latest)->where('severity', 'notice')->where('status', 'open')->count(),
            'ignoredIssues' => $this->issuesFor($latest)->where('status', 'ignored')->count(),
            'resolvedIssues' => $this->issuesFor($latest)->where('status', 'resolved')->count(),
            'externalBrokenLinks' => $latest?->external_broken_links_count ?? 0,
            'scoreHistory' => $this->scoreHistory(12),
            'commonIssueTypes' => $this->commonIssueTypes($latest),
            'topRedirects' => $this->topRedirects(),
            'worstPages' => $this->worstPages($latest),
            'pendingScans' => SeoScan::query()->pending()->count(),
            'runningScans' => SeoScan::query()->running()->count(),
            'failedScans' => SeoScan::query()->failed()->count(),
            'averageScore' => $scanIds === [] ? null : (int) round(SeoScan::query()->whereIn('id', $scanIds)->completed()->avg('score')),

        ];
    }

    /** @return array<string, mixed> */
    public function scanDetail(SeoScan $scan): array
    {
        $scan->loadMissing('previousScan');

        return [
            'scan' => $scan,
            'openIssues' => $scan->issues()->open()->count(),
            'criticalIssues' => $scan->issues()->open()->where('severity', 'error')->count(),
            'warningIssues' => $scan->issues()->open()->where('severity', 'warning')->count(),
            'noticeIssues' => $scan->issues()->open()->where('severity', 'notice')->count(),
            'ignoredIssues' => $scan->issues()->ignored()->count(),
            'manuallyResolvedIssues' => $scan->issues()->resolved()->count(),
            'issueTypes' => $scan->issues()->distinct()->orderBy('type')->pluck('type')->all(),
            'severityBreakdown' => $this->severityBreakdown($scan),
            'typeBreakdown' => $this->typeBreakdown($scan),
            'links' => $this->links($scan),
        ];
    }

    /** @return EloquentCollection<int, SeoScan> */
    public function scoreHistory(int $limit = 12): EloquentCollection
    {
        return SeoScan::query()
            ->latestFirst()
            ->limit($limit)
            ->completed()
            ->get(['id', 'score', 'score_delta', 'issues_count', 'created_at'])
            ->reverse()
            ->values();
    }

    /** @return Collection<int, object> */
    public function commonIssueTypes(?SeoScan $scan, int $limit = 8): Collection
    {
        return SeoScanIssue::query()
            ->when($scan, fn ($query) => $query->where('seo_scan_id', $scan->id))
            ->open()
            ->selectRaw('type, severity, count(*) as aggregate')
            ->groupBy('type', 'severity')
            ->orderByDesc('aggregate')
            ->limit($limit)
            ->get();
    }

    /** @return EloquentCollection<int, SeoRedirect> */
    public function topRedirects(int $limit = 5): EloquentCollection
    {
        return SeoRedirect::query()
            ->where('hits', '>', 0)
            ->orderByDesc('hits')
            ->limit($limit)
            ->get(['old_url', 'new_url', 'status_code', 'hits', 'last_hit_at']);
    }

    /** @return Collection<int, object> */
    public function worstPages(?SeoScan $scan, int $limit = 10): Collection
    {
        if (! $scan) {
            return collect();
        }

        return $scan->issues()
            ->open()
            ->whereNotNull('url')
            ->selectRaw('url, count(*) as issues_count, sum(case when severity = ? then 1 else 0 end) as errors_count', ['error'])
            ->groupBy('url')
            ->orderByDesc('errors_count')
            ->orderByDesc('issues_count')
            ->limit($limit)
            ->get();
    }

    /** @return Collection<int, object> */
    protected function severityBreakdown(SeoScan $scan): Collection
    {
        return $scan->issues()
            ->selectRaw('severity, status, count(*) as aggregate')
            ->groupBy('severity', 'status')
            ->orderBy('severity')
            ->get();
    }

    /** @return Collection<int, object> */
    protected function typeBreakdown(SeoScan $scan): Collection
    {
        return $scan->issues()
            ->open()
            ->selectRaw('type, severity, count(*) as aggregate')
            ->groupBy('type', 'severity')
            ->orderByDesc('aggregate')
            ->limit(20)
            ->get();
    }

    /** @return array<string, mixed> */
    protected function links(SeoScan $scan): array
    {
        $summary = $scan->summary ?? [];

        return [
            'broken' => $summary['broken_links'] ?? $summary['brokenLinks'] ?? [],
            'external_broken' => $summary['external_broken_links'] ?? $summary['externalBrokenLinks'] ?? [],
            'redirect_chains' => $summary['redirect_chains'] ?? $summary['redirectChains'] ?? [],
        ];
    }

    protected function issuesFor(?SeoScan $scan)
    {
        return SeoScanIssue::query()->when($scan, fn ($query) => $query->where('seo_scan_id', $scan->id));
    }
}
