<?php

namespace Step2dev\LazySeoTools\Http\Livewire;

use Livewire\Component;
use Step2dev\LazySeoTools\Models\SeoScan;

class SeoMonitoringDashboard extends Component
{
    public int $limit = 10;

    public function render()
    {
        $latest = SeoScan::query()->latestFirst()->first();
        $scans = SeoScan::query()->latestFirst()->limit($this->limit)->get();

        return view('lazy-seo::livewire.monitoring-dashboard', [
            'latest' => $latest,
            'scans' => $scans,
            'criticalIssues' => $latest?->issues()->where('severity', 'error')->count() ?? 0,
            'warningIssues' => $latest?->issues()->where('severity', 'warning')->count() ?? 0,
            'noticeIssues' => $latest?->issues()->where('severity', 'notice')->count() ?? 0,
        ]);
    }
}
