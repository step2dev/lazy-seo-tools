<?php

namespace Step2dev\LazySeoTools\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Models\SeoScanIssue;

class SeoIssuesTable extends Component
{
    use WithPagination;

    public ?int $scanId = null;
    public string $severity = '';
    public string $type = '';

    public function updatingSeverity(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $scanId = $this->scanId ?: SeoScan::query()->latestFirst()->value('id');

        $query = SeoScanIssue::query()
            ->when($scanId, fn ($query) => $query->where('seo_scan_id', $scanId))
            ->when($this->severity !== '', fn ($query) => $query->where('severity', $this->severity))
            ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
            ->latest();

        return view('lazy-seo::livewire.issues-table', [
            'issues' => $query->paginate(20),
            'scans' => SeoScan::query()->latestFirst()->limit(50)->get(),
            'currentScanId' => $scanId,
            'types' => SeoScanIssue::query()
                ->when($scanId, fn ($query) => $query->where('seo_scan_id', $scanId))
                ->distinct()
                ->orderBy('type')
                ->pluck('type')
                ->all(),
        ]);
    }
}
