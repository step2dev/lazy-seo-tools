<?php

use Step2dev\LazySeoTools\Models\SeoTemplate;
use Step2dev\LazySeoTools\Services\SeoManager;

it('renders seo data from template placeholders', function (): void {
    SeoTemplate::query()->create([
        'name' => 'post',
        'title' => ['en' => '{title} - {site_name}'],
        'description' => ['en' => 'Read {title} in {locale}'],
        'keywords' => ['en' => '{title}, seo'],
        'payload' => ['type' => 'article'],
        'enabled' => true,
    ]);

    $html = app(SeoManager::class)
        ->reset()
        ->template('post', ['title' => 'Lazy SEO'])
        ->render()
        ->toHtml();

    expect($html)
        ->toContain('<title>Lazy SEO - Laravel</title>')
        ->toContain('Read Lazy SEO in en')
        ->toContain('article');
});
