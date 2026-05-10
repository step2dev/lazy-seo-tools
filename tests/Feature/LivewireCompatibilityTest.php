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

    $finder = app('livewire.finder');

    expect($finder->getClass('lazy-seo-form'))->toBe(SeoForm::class)
        ->and($finder->getClass('lazy-seo-analyzer'))->toBe(SeoAnalyzerLivewire::class)
        ->and($finder->getClass('lazy-seo-redirect-table'))->toBe(RedirectTable::class)
        ->and($finder->getClass('lazy-seo-monitoring-dashboard'))->toBe(SeoMonitoringDashboard::class)
        ->and($finder->getClass('lazy-seo-issues-table'))->toBe(SeoIssuesTable::class)
        ->and($finder->getClass('lazy-seo-scan-detail'))->toBe(SeoScanDetail::class);
});
