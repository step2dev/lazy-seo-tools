<?php

use Illuminate\Support\Facades\Route;
use Step2dev\LazySeoTools\Http\Controllers\Api\SeoApiController;
use Step2dev\LazySeoTools\Http\Middleware\ForceHeadless;

if (config('lazy-seo.routes.api', false) && config('lazy-seo.features.api', true)) {
    Route::middleware(array_merge(config('lazy-seo.routes.api_middleware', ['api']), [ForceHeadless::class]))
        ->prefix(config('lazy-seo.routes.api_prefix', 'seo'))
        ->name('lazy-seo.api.')
        ->group(function () {
            Route::middleware(config('lazy-seo.routes.api_read_middleware', []))->group(function (): void {
                Route::get('/', [SeoApiController::class, 'index'])->name('index');
                Route::get('/{seo}', [SeoApiController::class, 'show'])->name('show');
            });

            Route::middleware(config('lazy-seo.routes.api_write_middleware', []))->group(function (): void {
                Route::post('/', [SeoApiController::class, 'store'])->name('store');
                Route::put('/{seo}', [SeoApiController::class, 'update'])->name('update');
                Route::delete('/{seo}', [SeoApiController::class, 'destroy'])->name('destroy');
            });
        });
}
