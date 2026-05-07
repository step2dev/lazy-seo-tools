<?php

namespace Step2dev\LazySeoTools\Http\Livewire;

use Livewire\Component;
use Step2dev\LazySeoTools\Models\SeoRedirect;
use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Models\SeoScanIssue;

class SeoMonitoringDashboard extends Component
{
    public int $limit = 10;

    public function render()
    {
        $latest = SeoScan::query()->latestFirst()->first();
        $scans = SeoScan::query()->latestFirst()->limit($this->limit)->get();
        $scanIds = $scans->pluck('id')->all();

        return view('lazy-seo::livewire.monitoring-dashboard', [
            'latest' => $latest,
            'scans' => $scans,
            'criticalIssues' => $latest?->issues()->where('severity', 'error')->count() ?? 0,
            'externalBrokenLinks' => $latest?->external_broken_links_count ?? 0,
            'warningIssues' => $latest?->issues()->where('severity', 'warning')->count() ?? 0,
            'noticeIssues' => $latest?->issues()->where('severity', 'notice')->count() ?? 0,
            'scoreHistory' => SeoScan::query()
                ->latestFirst()
                ->limit(12)
                ->get(['id', 'score', 'issues_count', 'created_at'])
                ->reverse()
                ->values(),
            'commonIssueTypes' => SeoScanIssue::query()
                ->when($latest, fn ($query) => $query->where('seo_scan_id', $latest->id))
                ->selectRaw('type, severity, count(*) as aggregate')
                ->groupBy('type', 'severity')
                ->orderByDesc('aggregate')
                ->limit(8)
                ->get(),
            'topRedirects' => SeoRedirect::query()
                ->where('hits', '>', 0)
                ->orderByDesc('hits')
                ->limit(5)
                ->get(['old_url', 'new_url', 'status_code', 'hits', 'last_hit_at']),
            'averageScore' => $scanIds === [] ? null : (int) round(SeoScan::query()->whereIn('id', $scanIds)->avg('score')),
        ]);
    }
}
