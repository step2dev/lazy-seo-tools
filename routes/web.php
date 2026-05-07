<?php

use Illuminate\Support\Facades\Route;

if (config('lazy-seo.routes.web', false)) {
    Route::middleware(['web'])
        ->prefix('lazy-seo')
        ->name('lazy-seo.')
        ->group(function () {
            Route::view('/analyzer', 'lazy-seo::livewire.analyzer')->name('analyzer');
            Route::view('/redirects', 'lazy-seo::livewire.redirect-table')->name('redirects');
        });
}
