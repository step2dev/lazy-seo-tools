<?php

use Illuminate\Support\Facades\Route;
use Step2dev\LazySeoTools\Models\SeoScan;

if (config('lazy-seo.routes.web', false)) {
    Route::middleware(config('lazy-seo.routes.admin_middleware', ['web']))
        ->prefix(config('lazy-seo.routes.admin_prefix', 'lazy-seo'))
        ->name('lazy-seo.')
        ->group(function () {
            Route::view('/dashboard', 'lazy-seo::admin.dashboard')->name('dashboard');
            Route::view('/issues', 'lazy-seo::admin.issues')->name('issues');
            Route::get('/scans/{scan}', fn (SeoScan $scan) => view('lazy-seo::admin.scan', ['scan' => $scan]))->name('scans.show');
            Route::view('/redirects', 'lazy-seo::admin.redirects')->name('redirects');
        });
}
