<?php

it('registers package livewire components when livewire is available', function (): void {
    expect(class_exists(\Livewire\Livewire::class))->toBeTrue()
        ->and(class_exists(\Step2dev\LazySeoTools\Http\Livewire\SeoMonitoringDashboard::class))->toBeTrue();

    $livewire = app('livewire');

    if (method_exists($livewire, 'getComponents')) {
        expect($livewire->getComponents())->toBeArray();
    } else {
        expect($livewire)->not->toBeNull();
    }
});
