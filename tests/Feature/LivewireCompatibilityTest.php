<?php

use Livewire\Livewire;
use Step2dev\LazySeoTools\Http\Livewire\RedirectTable;
use Step2dev\LazySeoTools\Http\Livewire\SeoAnalyzerLivewire;
use Step2dev\LazySeoTools\Http\Livewire\SeoForm;
use Step2dev\LazySeoTools\Http\Livewire\SeoIssuesTable;
use Step2dev\LazySeoTools\Http\Livewire\SeoMonitoringDashboard;
use Step2dev\LazySeoTools\Http\Livewire\SeoScanDetail;
use Step2dev\LazySeoTools\Models\SeoScan;

it('registers package livewire components when livewire is available', function (): void {
    if (! class_exists(Livewire::class)) {
        $this->markTestSkipped('Livewire is not installed.');
    }

    expect(Livewire::test('lazy-seo-form'))->component->toBeInstanceOf(SeoForm::class)
        ->and(Livewire::test('lazy-seo-analyzer'))->component->toBeInstanceOf(SeoAnalyzerLivewire::class)
        ->and(Livewire::test('lazy-seo-redirect-table'))->component->toBeInstanceOf(RedirectTable::class)
        ->and(Livewire::test('lazy-seo-monitoring-dashboard'))->component->toBeInstanceOf(SeoMonitoringDashboard::class)
        ->and(Livewire::test('lazy-seo-issues-table'))->component->toBeInstanceOf(SeoIssuesTable::class);

    $scan = SeoScan::query()->create([
        'url' => 'https://example.com',
        'status' => 'completed',
    ]);

    expect(Livewire::test('lazy-seo-scan-detail', ['scan' => $scan]))->component
        ->toBeInstanceOf(SeoScanDetail::class);
});
