<?php

use Illuminate\Support\Facades\Route;
use Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects;
use Step2dev\LazySeoTools\Models\SeoRedirect;

beforeEach(function (): void {
    Route::middleware(HandleSeoRedirects::class)->any('/old-page', fn () => 'old');
    Route::middleware(HandleSeoRedirects::class)->any('/gone-page', fn () => 'gone');
});

it('redirects exact enabled urls and tracks hits atomically', function (): void {
    $redirect = SeoRedirect::query()->create([
        'old_url' => '/old-page',
        'new_url' => '/new-page',
        'status_code' => 301,
        'enabled' => true,
    ]);

    $this->get('/old-page?utm=test')
        ->assertRedirect('/new-page?utm=test')
        ->assertStatus(301);

    expect($redirect->refresh()->hits)->toBe(1);
});

it('returns gone responses', function (): void {
    SeoRedirect::query()->create([
        'old_url' => '/gone-page',
        'new_url' => null,
        'status_code' => 410,
        'enabled' => true,
    ]);

    $this->get('/gone-page')->assertGone();
});
