<?php

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Step2dev\LazySeoTools\Models\Seo;

beforeEach(function (): void {
    config()->set('lazy-seo.routes.api', true);
    config()->set('lazy-seo.features.api', true);
    config()->set('lazy-seo.routes.api_prefix', 'seo');

    if (! Route::has('lazy-seo.api.store')) {
        require __DIR__.'/../../routes/api.php';
        Route::getRoutes()->refreshNameLookups();
    }

    $this->withoutMiddleware(Authenticate::class);
});

it('stores seo records without allowing morph binding by default', function (): void {
    $response = $this->postJson(route('lazy-seo.api.store'), [
        'url' => '/about',
        'title' => ['en' => 'About'],
        'description' => ['en' => 'About page'],
        'seoable_type' => 'App\\Models\\User',
        'seoable_id' => 1,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.url', '/about')
        ->assertJsonMissingPath('data.seoable_type')
        ->assertJsonMissingPath('data.seoable_id');

    expect(Seo::query()->first())
        ->seoable_type->toBeNull()
        ->seoable_id->toBeNull();
});

it('updates seo records partially', function (): void {
    $seo = Seo::query()->create([
        'url' => '/about',
        'title' => ['en' => 'Old'],
        'indexable' => true,
    ]);

    $this->putJson(route('lazy-seo.api.update', $seo), [
        'title' => ['en' => 'New'],
    ])->assertOk()->assertJsonPath('data.title.en', 'New');
});
