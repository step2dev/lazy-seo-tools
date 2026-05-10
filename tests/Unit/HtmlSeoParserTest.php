<?php

use Step2dev\LazySeoTools\Services\HtmlSeoParser;
use Step2dev\LazySeoTools\Services\UrlNormalizer;

it('parses seo data with dom instead of fragile attribute-order regex', function (): void {
    $parser = new HtmlSeoParser(new UrlNormalizer);

    $parsed = $parser->parse(<<<'HTML'
<!doctype html>
<html>
<head>
    <meta content="A clean description" name="description">
    <meta content="index, follow" name="robots">
    <meta content="https://cdn.example.com/og.jpg" property="og:image">
    <meta content="Twitter title" name="twitter:title">
    <link href="/canonical-page" rel="preload canonical">
    <title>  Test &amp; Page  </title>
</head>
<body>
    <h1>Main heading</h1>
    <h2>Second heading</h2>
    <a href="/about"> About us </a>
    <img src="/cover.jpg" alt="Cover image">
</body>
</html>
HTML, 'https://example.com/current');

    expect($parsed['title'])->toBe('Test & Page')
        ->and($parsed['description'])->toBe('A clean description')
        ->and($parsed['canonical'])->toBe('/canonical-page')
        ->and($parsed['robots'])->toBe(['index', 'follow'])
        ->and($parsed['image'])->toBe('https://cdn.example.com/og.jpg')
        ->and($parsed['has_twitter'])->toBeTrue()
        ->and($parsed['headings'])->toHaveCount(2)
        ->and($parsed['links'][0]['url'])->toBe('https://example.com/about')
        ->and($parsed['images'][0])->toBe(['src' => 'https://example.com/cover.jpg', 'alt' => 'Cover image']);
});

it('keeps parsing malformed html safely', function (): void {
    $parser = new HtmlSeoParser(new UrlNormalizer);

    $parsed = $parser->parse('<title>Broken<title><h1>Still works<a href="/x">X', 'https://example.com');

    expect($parsed['title'])->toContain('Broken')
        ->and($parsed['headings'][0]['text'])->toContain('Still works')
        ->and($parsed['links'][0]['url'])->toBe('https://example.com/x');
});
