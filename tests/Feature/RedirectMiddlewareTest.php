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
