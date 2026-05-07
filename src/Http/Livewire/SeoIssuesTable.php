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

    public string $status = 'open';

    public string $search = '';

    /** @var array<int, bool> */
    public array $selected = [];

    public function updatingSeverity(): void { $this->resetPage(); }
    public function updatingType(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingSearch(): void { $this->resetPage(); }

    public function markSelectedResolved(): void
    {
        $this->selectedIssues()->each->markResolved();
        $this->selected = [];
    }

    public function ignoreSelected(): void
    {
        $this->selectedIssues()->each->markIgnored();
        $this->selected = [];
    }

    public function reopenSelected(): void
    {
        $this->selectedIssues()->each->reopen();
        $this->selected = [];
    }

    public function render()
    {
        $scanId = $this->scanId ?: SeoScan::query()->latestFirst()->value('id');

        $query = SeoScanIssue::query()
            ->when($scanId, fn ($query) => $query->where('seo_scan_id', $scanId))
            ->when($this->severity !== '', fn ($query) => $query->where('severity', $this->severity))
            ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($query): void {
                    $query->where('url', 'like', "%{$this->search}%")
                        ->orWhere('message', 'like', "%{$this->search}%")
                        ->orWhere('type', 'like', "%{$this->search}%");
                });
            })
            ->orderByRaw("case severity when 'error' then 1 when 'warning' then 2 else 3 end")
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

    protected function selectedIssues()
    {
        $ids = collect($this->selected)->filter()->keys()->map(fn ($id) => (int) $id)->values()->all();

        return SeoScanIssue::query()
            ->when($this->scanId, fn ($query) => $query->where('seo_scan_id', $this->scanId))
            ->whereKey($ids)
            ->get();
    }
}
