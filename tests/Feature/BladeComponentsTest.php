<?php

use Illuminate\Support\Facades\Blade;
use Step2dev\LazySeoTools\Models\Seo;

it('renders meta component', function () {
    app()->setLocale('en');

    $seo = Seo::create([
        'url' => '/component-test',
        'title' => ['en' => 'Component title'],
        'description' => ['en' => 'Component description'],
        'indexable' => true,
    ]);

    $html = Blade::render('<x-lazy-seo-meta :seo="$seo" />', ['seo' => $seo]);

    expect($html)->toContain('<title>Component title</title>')
        ->and($html)->toContain('Component description');
});
