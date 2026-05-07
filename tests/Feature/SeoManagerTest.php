<?php

use Step2dev\LazySeoTools\Facades\Seo as SeoFacade;
use Step2dev\LazySeoTools\Models\Seo;
use Step2dev\LazySeoTools\Services\SeoManager;

it('resolves seo manager through container and facade', function () {
    expect(app(SeoManager::class))->toBeInstanceOf(SeoManager::class)
        ->and(SeoFacade::getFacadeRoot())->toBeInstanceOf(SeoManager::class);
});

it('finds seo by url and renders meta tags', function () {
    $seo = Seo::create([
        'url' => '/about',
        'title' => ['en' => 'About'],
        'description' => ['en' => 'About description'],
        'keywords' => ['en' => 'about, company'],
        'indexable' => true,
    ]);

    app()->setLocale('en');

    $html = app(SeoManager::class)->renderMetaTags($seo)->toHtml();

    expect(app(SeoManager::class)->forUrl('/about')->id)->toBe($seo->id)
        ->and($html)->toContain('<title>About</title>')
        ->and($html)->toContain('About description');
});
