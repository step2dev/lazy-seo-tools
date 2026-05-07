<?php

use Illuminate\Support\Facades\Route;

if (config('lazy-seo.routes.web', false)) {
    Route::middleware(config('lazy-seo.routes.admin_middleware', ['web']))
        ->prefix(config('lazy-seo.routes.admin_prefix', 'lazy-seo'))
        ->name('lazy-seo.')
        ->group(function () {
            Route::view('/dashboard', 'lazy-seo::admin.dashboard')->name('dashboard');
            Route::view('/issues', 'lazy-seo::admin.issues')->name('issues');
            Route::view('/redirects', 'lazy-seo::admin.redirects')->name('redirects');
        });
}
