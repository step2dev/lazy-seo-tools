<?php

namespace Step2dev\LazySeoTools\Http\Livewire;

use Livewire\Component;
use Step2dev\LazySeoTools\Services\SeoDashboardService;

class SeoMonitoringDashboard extends Component
{
    public int $limit = 10;

    public function render()
    {
        return view('lazy-seo::livewire.monitoring-dashboard', app(SeoDashboardService::class)->overview($this->limit));
    }
}
