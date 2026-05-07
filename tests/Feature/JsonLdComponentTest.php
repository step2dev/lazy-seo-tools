<?php

use Illuminate\Support\Facades\Blade;

it('renders json ld blade component', function (): void {
    $html = Blade::render('<x-lazy-seo-jsonld type="article" :data="$data" />', [
        'data' => [
            'title' => 'Laravel SEO Tools',
            'description' => 'SEO toolkit for Laravel.',
        ],
    ]);

    expect($html)->toContain('application/ld+json')
        ->and($html)->toContain('Article')
        ->and($html)->toContain('Laravel SEO Tools');
});
