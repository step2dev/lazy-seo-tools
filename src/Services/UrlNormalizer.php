<?php

namespace Step2dev\LazySeoTools\Services;

class UrlNormalizer
{
    public function normalize(string $url, ?string $baseUrl = null): ?string
    {
        $url = trim(html_entity_decode($url));

        if ($url === '' || str_starts_with($url, '#') || preg_match('/^(mailto|tel|javascript):/i', $url)) {
            return null;
        }

        if (str_starts_with($url, '//')) {
            $scheme = $baseUrl ? parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https' : 'https';
            $url = $scheme.':'.$url;
        }

        if (! preg_match('#^https?://#i', $url)) {
            if (! $baseUrl) {
                return null;
            }

            $base = parse_url($baseUrl);
            $scheme = $base['scheme'] ?? 'https';
            $host = $base['host'] ?? null;

            if (! $host) {
                return null;
            }

            if (str_starts_with($url, '/')) {
                $url = $scheme.'://'.$host.$url;
            } else {
                $basePath = isset($base['path']) ? rtrim(dirname($base['path']), '/').'/' : '/';
                $url = $scheme.'://'.$host.$basePath.$url;
            }
        }

        $parts = parse_url($url);

        if (! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $path = $this->normalizePath($parts['path'] ?? '/');
        $query = isset($parts['query']) && $parts['query'] !== '' ? '?'.$parts['query'] : '';

        return strtolower($parts['scheme']).'://'.strtolower($parts['host']).$path.$query;
    }

    public function sameHost(string $url, string $baseUrl): bool
    {
        return strtolower((string) parse_url($url, PHP_URL_HOST)) === strtolower((string) parse_url($baseUrl, PHP_URL_HOST));
    }

    protected function normalizePath(string $path): string
    {
        $segments = [];

        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);
                continue;
            }

            $segments[] = rawurlencode(rawurldecode($segment));
        }

        return '/'.implode('/', $segments);
    }
}
