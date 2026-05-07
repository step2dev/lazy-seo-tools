<?php

use Step2dev\LazySeoTools\Models\Seo;

it('stores seo records without allowing morph binding by default', function (): void {
    $response = $this->postJson('/seo', [
        'url' => '/about',
        'title' => ['en' => 'About'],
        'description' => ['en' => 'About page'],
        'seoable_type' => 'App\\Models\\User',
        'seoable_id' => 1,
    ]);

    $response->assertCreated()
        ->assertJsonPath('url', '/about')
        ->assertJsonMissingPath('seoable_type')
        ->assertJsonMissingPath('seoable_id');

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
    ])->assertOk()->assertJsonPath('title.en', 'New');
});
