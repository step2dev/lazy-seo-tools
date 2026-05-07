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

it('supports a simple page API with presets and meta alias', function (): void {
    $html = app(SeoManager::class)
        ->reset()
        ->preset('article', [
            'title' => 'Simple article',
            'excerpt' => 'A simple article description.',
            'cover_url' => 'https://example.com/cover.jpg',
            'url' => 'https://example.com/blog/simple-article',
        ])
        ->meta()
        ->toHtml();

    expect($html)
        ->toContain('<title>Simple article</title>')
        ->toContain('A simple article description.')
        ->toContain('<link rel="canonical" href="https://example.com/blog/simple-article">')
        ->toContain('<meta property="og:type" content="article">')
        ->toContain('https://example.com/cover.jpg');
});

it('can select a url source and merge overrides fluently', function (): void {
    Seo::create([
        'url' => '/pricing',
        'title' => ['en' => 'Stored pricing'],
        'description' => ['en' => 'Stored pricing description'],
        'indexable' => true,
    ]);

    app()->setLocale('en');

    $data = app(SeoManager::class)
        ->reset()
        ->for('/pricing')
        ->with(['title' => 'Runtime pricing'])
        ->data();

    expect($data->title)->toBe('Runtime pricing')
        ->and($data->description)->toBe('Stored pricing description');
});
