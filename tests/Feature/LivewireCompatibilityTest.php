<?php

use Livewire\Livewire;
use Step2dev\LazySeoTools\Http\Livewire\RedirectTable;
use Step2dev\LazySeoTools\Http\Livewire\SeoAnalyzerLivewire;
use Step2dev\LazySeoTools\Http\Livewire\SeoForm;
use Step2dev\LazySeoTools\Http\Livewire\SeoIssuesTable;
use Step2dev\LazySeoTools\Http\Livewire\SeoMonitoringDashboard;
use Step2dev\LazySeoTools\Http\Livewire\SeoScanDetail;

it('registers package livewire components when livewire is available', function (): void {
    if (! class_exists(Livewire::class)) {
        $this->markTestSkipped('Livewire is not installed.');
    }

    expect(Livewire::getClass('lazy-seo-form'))->toBe(SeoForm::class)
        ->and(Livewire::getClass('lazy-seo-analyzer'))->toBe(SeoAnalyzerLivewire::class)
        ->and(Livewire::getClass('lazy-seo-redirect-table'))->toBe(RedirectTable::class)
        ->and(Livewire::getClass('lazy-seo-monitoring-dashboard'))->toBe(SeoMonitoringDashboard::class)
        ->and(Livewire::getClass('lazy-seo-issues-table'))->toBe(SeoIssuesTable::class)
        ->and(Livewire::getClass('lazy-seo-scan-detail'))->toBe(SeoScanDetail::class);
});
