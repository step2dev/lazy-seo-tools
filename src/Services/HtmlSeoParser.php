<?php

namespace Step2dev\LazySeoTools\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;

class HtmlSeoParser
{
    public function __construct(protected UrlNormalizer $urls) {}

    /** @return array{title: ?string, description: ?string, canonical: ?string, robots: array<int, string>, image: ?string, has_og: bool, has_twitter: bool, headings: array<int, array{level: int, text: string}>, links: array<int, array{url: string, text: string, external: bool}>, images: array<int, array{src: string, alt: string}>} */
    public function parse(string $html, string $baseUrl): array
    {
        $xpath = $this->xpath($html);
        $robots = array_map('trim', explode(',', strtolower((string) $this->metaContent($xpath, 'robots'))));

        return [
            'title' => $this->text($xpath, '//title[1]'),
            'description' => $this->metaContent($xpath, 'description'),
            'canonical' => $this->linkHref($xpath, 'canonical'),
            'robots' => array_values(array_filter($robots)),
            'image' => $this->metaProperty($xpath, 'og:image') ?: $this->metaContent($xpath, 'twitter:image'),
            'has_og' => filled($this->metaProperty($xpath, 'og:title')),
            'has_twitter' => filled($this->metaContent($xpath, 'twitter:title')),
            'headings' => $this->headings($xpath, $html),
            'links' => $this->links($xpath, $baseUrl, $html),
            'images' => $this->images($xpath, $baseUrl, $html),
        ];
    }

    protected function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);

        $flags = LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_NONET;

        if (defined('LIBXML_COMPACT')) {
            $flags |= LIBXML_COMPACT;
        }

        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html, $flags);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    protected function text(DOMXPath $xpath, string $query): ?string
    {
        $node = $xpath->query($query)?->item(0);

        if (! $node) {
            return null;
        }

        return $this->clean($node->textContent);
    }

    protected function metaContent(DOMXPath $xpath, string $name): ?string
    {
        return $this->attribute(
            $xpath,
            '//meta[translate(@name, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = '.
            $this->literal(strtolower($name)).']/@content'
        );
    }

    protected function metaProperty(DOMXPath $xpath, string $property): ?string
    {
        return $this->attribute(
            $xpath,
            '//meta[translate(@property, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = '.
            $this->literal(strtolower($property)).']/@content'
        );
    }

    protected function linkHref(DOMXPath $xpath, string $rel): ?string
    {
        return $this->attribute(
            $xpath,
            '//link[contains(concat(" ", normalize-space(translate(@rel, "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz")), " "), '.
            $this->literal(' '.strtolower($rel).' ').')]/@href'
        );
    }

    protected function attribute(DOMXPath $xpath, string $query): ?string
    {
        $node = $xpath->query($query)?->item(0);

        if (! $node) {
            return null;
        }

        return $this->clean($node->nodeValue);
    }

    /** @return array<int, array{level: int, text: string}> */
    protected function headings(DOMXPath $xpath, string $html): array
    {
        $headings = [];

        foreach ($xpath->query('//h1|//h2|//h3|//h4|//h5|//h6') ?: [] as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $headings[] = [
                'level' => (int) substr($node->tagName, 1),
                'text' => $this->clean($node->textContent) ?? '',
            ];
        }

        return $headings !== [] ? $headings : $this->fallbackHeadings($html);
    }

    /** @return array<int, array{url: string, text: string, external: bool}> */
    protected function links(DOMXPath $xpath, string $baseUrl, string $html): array
    {
        $links = [];

        foreach ($xpath->query('//a[@href]') ?: [] as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $url = $this->urls->normalize($node->getAttribute('href'), $baseUrl);

            if (! $url) {
                continue;
            }

            $links[] = [
                'url' => $url,
                'text' => $this->clean($node->textContent) ?? '',
                'external' => ! $this->urls->sameHost($url, $baseUrl),
            ];
        }

        return $links !== [] ? $links : $this->fallbackLinks($html, $baseUrl);
    }

    /** @return array<int, array{src: string, alt: string}> */
    protected function images(DOMXPath $xpath, string $baseUrl, string $html): array
    {
        $images = [];

        foreach ($xpath->query('//img[@src]') ?: [] as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $src = $this->urls->normalize($node->getAttribute('src'), $baseUrl);

            if (! $src) {
                continue;
            }

            $images[] = [
                'src' => $src,
                'alt' => $this->clean($node->getAttribute('alt')) ?? '',
            ];
        }

        return $images !== [] ? $images : $this->fallbackImages($html, $baseUrl);
    }

    /** @return array<int, array{level: int, text: string}> */
    protected function fallbackHeadings(string $html): array
    {
        preg_match_all('/<h([1-6])\b[^>]*>(.*?)(?=<h[1-6]\b|<\/h\1>|$)/isu', $html, $matches, PREG_SET_ORDER);

        $headings = [];

        foreach ($matches as $match) {
            $text = $this->clean(strip_tags($match[2]));

            if ($text === null) {
                continue;
            }

            $headings[] = [
                'level' => (int) $match[1],
                'text' => $text,
            ];
        }

        return $headings;
    }

    /** @return array<int, array{url: string, text: string, external: bool}> */
    protected function fallbackLinks(string $html, string $baseUrl): array
    {
        preg_match_all('/<a\b[^>]*href=(?P<quote>["\']?)([^"\'\s>]+)\k<quote>[^>]*>(.*?)(?=<a\b|<\/a>|$)/isu', $html, $matches, PREG_SET_ORDER);

        $links = [];

        foreach ($matches as $match) {
            $url = $this->urls->normalize($match[2], $baseUrl);

            if (! $url) {
                continue;
            }

            $links[] = [
                'url' => $url,
                'text' => $this->clean(strip_tags($match[3])) ?? '',
                'external' => ! $this->urls->sameHost($url, $baseUrl),
            ];
        }

        return $links;
    }

    /** @return array<int, array{src: string, alt: string}> */
    protected function fallbackImages(string $html, string $baseUrl): array
    {
        preg_match_all('/<img\b[^>]*src=(?P<quote>["\']?)([^"\'\s>]+)\k<quote>[^>]*>/isu', $html, $matches, PREG_SET_ORDER);

        $images = [];

        foreach ($matches as $match) {
            $src = $this->urls->normalize($match[2], $baseUrl);

            if (! $src) {
                continue;
            }

            $images[] = [
                'src' => $src,
                'alt' => $this->extractAttribute($match[0], 'alt') ?? '',
            ];
        }

        return $images;
    }

    protected function extractAttribute(string $html, string $attribute): ?string
    {
        if (! preg_match('/\s'.preg_quote($attribute, '/').'=(?P<quote>["\']?)([^"\'\s>]*)\k<quote>/isu', $html, $match)) {
            return null;
        }

        return $this->clean($match[2]);
    }

    protected function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return $value === '' ? null : $value;
    }

    protected function literal(string $value): string
    {
        if (! str_contains($value, "'")) {
            return "'{$value}'";
        }

        if (! str_contains($value, '"')) {
            return '"'.$value.'"';
        }

        return 'concat(\''.str_replace("'", "', \"'\", '", $value).'\')';
    }
}
