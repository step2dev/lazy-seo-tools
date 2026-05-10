<?php

use Illuminate\Auth\Middleware\Authenticate;
use Step2dev\LazySeoTools\Models\Seo;

beforeEach(function (): void {
    $this->withoutMiddleware(Authenticate::class);
});

it('stores seo records without allowing morph binding by default', function (): void {
    $response = $this->postJson('/seo', [
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

    $this->putJson('/seo/'.$seo->getKey(), [
        'title' => ['en' => 'New'],
    ])->assertOk()->assertJsonPath('data.title.en', 'New');
});
