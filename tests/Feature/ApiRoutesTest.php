<?php

use Illuminate\Support\Facades\Route;
use Step2dev\LazySeoTools\Http\Controllers\Api\SeoApiController;
use Step2dev\LazySeoTools\Http\Middleware\ForceHeadless;
use Step2dev\LazySeoTools\Models\Seo;

beforeEach(function (): void {
    config()->set('lazy-seo.routes.api_allow_morph_binding', false);

    Route::middleware(['api', ForceHeadless::class])
        ->prefix('seo')
        ->name('lazy-seo.api.')
        ->group(function (): void {
            Route::get('/', [SeoApiController::class, 'index'])->name('index');
            Route::get('/{seo}', [SeoApiController::class, 'show'])->name('show');
            Route::post('/', [SeoApiController::class, 'store'])->name('store');
            Route::put('/{seo}', [SeoApiController::class, 'update'])->name('update');
            Route::delete('/{seo}', [SeoApiController::class, 'destroy'])->name('destroy');
        });
});

it('returns seo records through a stable json resource envelope', function (): void {
    Seo::query()->create([
        'url' => '/about',
        'title' => ['en' => 'About'],
        'description' => ['en' => 'About page'],
        'robots' => ['index', 'follow'],
        'indexable' => true,
    ]);

    $this->getJson('/seo')
        ->assertOk()
        ->assertJsonPath('data.0.url', '/about')
        ->assertJsonPath('data.0.indexable', true);
});

it('limits api pagination per page to a safe maximum', function (): void {
    $this->getJson('/seo?per_page=500')
        ->assertOk()
        ->assertJsonPath('meta.per_page', 100);
});

it('does not expose morph binding fields unless explicitly enabled', function (): void {
    $seo = Seo::query()->create([
        'url' => '/about',
        'seoable_type' => 'App\\Models\\Post',
        'seoable_id' => 1,
    ]);

    $this->getJson('/seo/'.$seo->id)
        ->assertOk()
        ->assertJsonMissingPath('data.seoable_type')
        ->assertJsonMissingPath('data.seoable_id');
});

it('rejects morph binding types outside the configured whitelist', function (): void {
    config()->set('lazy-seo.routes.api_allow_morph_binding', true);
    config()->set('lazy-seo.routes.api_allowed_seoable_types', [Step2dev\LazySeoTools\Models\Seo::class]);

    $this->postJson('/seo', [
        'url' => '/about',
        'title' => ['en' => 'About'],
        'seoable_type' => 'App\\Models\\Post',
        'seoable_id' => 1,
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['seoable_type']);
});

it('accepts morph binding types from the configured whitelist', function (): void {
    config()->set('lazy-seo.routes.api_allow_morph_binding', true);
    config()->set('lazy-seo.routes.api_allowed_seoable_types', [Step2dev\LazySeoTools\Models\Seo::class]);

    $this->postJson('/seo', [
        'url' => '/about',
        'title' => ['en' => 'About'],
        'seoable_type' => Step2dev\LazySeoTools\Models\Seo::class,
        'seoable_id' => 123,
    ])->assertCreated()
        ->assertJsonPath('data.seoable_type', Step2dev\LazySeoTools\Models\Seo::class)
        ->assertJsonPath('data.seoable_id', 123);
});

it('ignores morph binding payload when morph binding is disabled', function (): void {
    config()->set('lazy-seo.routes.api_allow_morph_binding', false);
    config()->set('lazy-seo.routes.api_allowed_seoable_types', [Step2dev\LazySeoTools\Models\Seo::class]);

    $this->postJson('/seo', [
        'url' => '/ignored-morph',
        'title' => ['en' => 'Ignored morph'],
        'seoable_type' => Step2dev\LazySeoTools\Models\Seo::class,
        'seoable_id' => 123,
    ])->assertCreated()
        ->assertJsonMissingPath('data.seoable_type')
        ->assertJsonMissingPath('data.seoable_id');
});
