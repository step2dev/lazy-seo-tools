<?php

use Step2dev\LazySeoTools\Services\JsonLdService;
use Step2dev\LazySeoTools\Services\SchemaService;

it('builds article schema', function (): void {
    $schema = app(SchemaService::class)->make('article', [
        'title' => 'Laravel SEO Tools',
        'description' => 'SEO toolkit for Laravel.',
        'author' => 'Step2Dev',
        'url' => 'https://example.com/blog/seo',
    ]);

    expect($schema['@context'])->toBe('https://schema.org')
        ->and($schema['@type'])->toBe('Article')
        ->and($schema['headline'])->toBe('Laravel SEO Tools')
        ->and($schema['author']['name'])->toBe('Step2Dev');
});

it('builds breadcrumb list schema', function (): void {
    $schema = app(SchemaService::class)->make('breadcrumbs', [
        'items' => [
            ['name' => 'Home', 'url' => 'https://example.com'],
            ['name' => 'Blog', 'url' => 'https://example.com/blog'],
        ],
    ]);

    expect($schema['@type'])->toBe('BreadcrumbList')
        ->and($schema['itemListElement'])->toHaveCount(2)
        ->and($schema['itemListElement'][1]['position'])->toBe(2);
});

it('renders json ld script', function (): void {
    $html = app(JsonLdService::class)->script('faq', [
        'items' => [
            ['question' => 'What is Lazy SEO?', 'answer' => 'A Laravel SEO toolkit.'],
        ],
    ]);

    expect($html)->toContain('application/ld+json')
        ->and($html)->toContain('FAQPage')
        ->and($html)->toContain('What is Lazy SEO?');
});
