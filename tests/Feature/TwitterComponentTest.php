<?php

use Illuminate\Support\Facades\Blade;
use Step2dev\LazySeoTools\Models\Seo;

it('renders twitter component', function (): void {
    app()->setLocale('en');

    $seo = Seo::create([
        'url' => '/twitter-test',
        'title' => ['en' => 'Twitter title'],
        'description' => ['en' => 'Twitter description'],
        'indexable' => true,
    ]);

    $html = Blade::render('<x-lazy-seo-twitter :seo="$seo" />', ['seo' => $seo]);

    expect($html)->toContain('twitter:title')
        ->and($html)->toContain('Twitter title');
});
