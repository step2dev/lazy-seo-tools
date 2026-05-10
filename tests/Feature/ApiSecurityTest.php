<?php

use Illuminate\Support\Facades\Route;
use Step2dev\LazySeoTools\Http\Controllers\Api\SeoApiController;
use Step2dev\LazySeoTools\Http\Middleware\ForceHeadless;

beforeEach(function (): void {
    Route::middleware(array_merge(config('lazy-seo.routes.api_middleware', ['api']), [ForceHeadless::class]))
        ->prefix('secure-seo')
        ->name('lazy-seo.secure-api.')
        ->group(function (): void {
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

    Route::getRoutes()->refreshNameLookups();
});

it('registers api read routes with the configured auth middleware by default', function (): void {
    $route = Route::getRoutes()->getByName('lazy-seo.secure-api.index');

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())
        ->toContain('auth:sanctum');
});

it('registers api write routes with the configured auth middleware by default', function (): void {
    $route = Route::getRoutes()->getByName('lazy-seo.secure-api.store');

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())
        ->toContain('auth:sanctum');
});
