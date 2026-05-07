<?php

use Illuminate\Support\Facades\Route;
use Step2dev\LazySeoTools\Http\Middleware\HandleSeoRedirects;
use Step2dev\LazySeoTools\Models\SeoRedirect;

it('redirects exact urls', function () {
    Route::middleware(HandleSeoRedirects::class)->get('/old', fn () => 'old');

    SeoRedirect::create([
        'old_url' => 'old',
        'new_url' => '/new',
        'status_code' => 301,
    ]);

    $this->get('/old')->assertRedirect('/new');
});

it('supports gone redirects', function () {
    Route::middleware(HandleSeoRedirects::class)->get('/removed', fn () => 'removed');

    SeoRedirect::create([
        'old_url' => 'removed',
        'status_code' => 410,
    ]);

    $this->get('/removed')->assertGone();
});


it('tracks redirect hits', function () {
    Route::middleware(HandleSeoRedirects::class)->get('/track-old', fn () => 'old');

    $redirect = SeoRedirect::create([
        'old_url' => 'track-old',
        'new_url' => '/track-new',
        'status_code' => 302,
    ]);

    $this->get('/track-old')->assertRedirect('/track-new');

    expect($redirect->refresh()->hits)->toBe(1)
        ->and($redirect->last_hit_at)->not->toBeNull();
});
