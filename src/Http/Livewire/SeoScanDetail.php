<?php

namespace Step2dev\LazySeoTools\Http\Livewire;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Livewire\Component;
use Livewire\WithPagination;
use Step2dev\LazySeoTools\Models\SeoScan;
use Step2dev\LazySeoTools\Models\SeoScanIssue;
use Step2dev\LazySeoTools\Services\SeoDashboardService;

class SeoScanDetail extends Component
{
    use WithPagination;

    public SeoScan $scan;

    public string $severity = '';

    public string $type = '';

    public string $status = 'open';

    public string $search = '';

    /** @var array<int, bool> */
    public array $selected = [];

    public function mount(SeoScan $scan): void
    {
        $this->scan = $scan;
    }

    public function updatingSeverity(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function markIssueResolved(int $issueId): void
    {
        $this->issueQuery()->whereKey($issueId)->firstOrFail()->markResolved();
    }

    public function ignoreIssue(int $issueId): void
    {
        $this->issueQuery()->whereKey($issueId)->firstOrFail()->markIgnored();
    }

    public function reopenIssue(int $issueId): void
    {
        $this->issueQuery()->whereKey($issueId)->firstOrFail()->reopen();
    }

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
        $data = app(SeoDashboardService::class)->scanDetail($this->scan);

        return app(ViewFactory::class)->make('lazy-seo::livewire.scan-detail', $data + [
            'issues' => $this->filteredIssues()->paginate(25),
        ]);
    }

    protected function filteredIssues()
    {
        return $this->issueQuery()
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
    }

    protected function issueQuery()
    {
        return SeoScanIssue::query()->where('seo_scan_id', $this->scan->id);
    }

    protected function selectedIssues()
    {
        $ids = collect($this->selected)
            ->filter()
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return $this->issueQuery()->whereKey($ids)->get();
    }
}
