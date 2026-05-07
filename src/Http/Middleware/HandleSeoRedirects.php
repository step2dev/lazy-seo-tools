<?php

namespace Step2dev\LazySeoTools\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Step2dev\LazySeoTools\Models\SeoRedirect;
use Symfony\Component\HttpFoundation\Response;

class HandleSeoRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('lazy-seo.redirects.enabled', true)) {
            return $next($request);
        }

        $redirect = $this->findRedirect($request);

        if (! $redirect) {
            return $next($request);
        }

        $this->markHit($redirect);

        if ($redirect->status_code === 410) {
            abort(410, 'Gone');
        }

        if (! $redirect->new_url || $this->isRedirectLoop($redirect->new_url, $request)) {
            return $next($request);
        }

        return redirect()->to($this->targetUrl($this->resolvedTarget($redirect, $request), $request), $redirect->status_code);
    }

    protected function findRedirect(Request $request): ?SeoRedirect
    {
        $allowedStatusCodes = config('lazy-seo.redirects.allowed_status_codes', [301, 302, 307, 308, 410]);
        $normalizedPath = SeoRedirect::normalizePath($request->path());

        $query = SeoRedirect::query()
            ->enabled()
            ->whereIn('status_code', $allowedStatusCodes);

        $redirect = (clone $query)
            ->exact()
            ->whereIn('normalized_old_url_hash', array_map('sha1', $this->exactVariants($request, $normalizedPath)))
            ->orderByDesc('id')
            ->first();

        if ($redirect) {
            return $redirect;
        }

        return $this->findPatternRedirect($request, $query);
    }

    /**
     * @return array<int, string>
     */
    protected function exactVariants(Request $request, string $normalizedPath): array
    {
        return array_values(array_unique(array_filter([
            $normalizedPath,
            SeoRedirect::normalizePath($request->getRequestUri()),
            SeoRedirect::normalizePath($request->fullUrl()),
        ])));
    }

    protected function findPatternRedirect(Request $request, $baseQuery): ?SeoRedirect
    {
        if (! config('lazy-seo.redirects.wildcard_enabled', true) && ! config('lazy-seo.redirects.regex_enabled', true)) {
            return null;
        }

        return $this->patternRedirects($baseQuery)
            ->first(fn (SeoRedirect $item): bool => $this->patternMatches($item, $request));
    }

    protected function patternRedirects($baseQuery)
    {
        $cacheSeconds = (int) config('lazy-seo.redirects.cache_seconds', 60);
        $callback = fn () => (clone $baseQuery)->pattern()->orderByDesc('id')->get();

        if ($cacheSeconds <= 0) {
            return $callback();
        }

        return Cache::remember('lazy-seo.redirects.patterns', $cacheSeconds, $callback);
    }

    protected function patternMatches(SeoRedirect $redirect, Request $request): bool
    {
        $path = trim($request->path(), '/');

        if (! $redirect->is_regex && config('lazy-seo.redirects.wildcard_enabled', true)) {
            return $this->wildcardMatches($redirect->old_url, $path);
        }

        return $redirect->is_regex
            && config('lazy-seo.redirects.regex_enabled', true)
            && $this->regexMatches($redirect->old_url, $path, $request);
    }

    protected function wildcardMatches(string $pattern, string $path): bool
    {
        $pattern = trim($pattern, '/');
        $regex = '#^'.str_replace('\\*', '.*', preg_quote($pattern, '#')).'$#u';

        return (bool) preg_match($regex, $path);
    }

    protected function regexMatches(string $pattern, string $path, Request $request): bool
    {
        $regex = $this->normalizeRegex($pattern);

        if ($regex === null) {
            return false;
        }

        return (bool) preg_match($regex, $path) || (bool) preg_match($regex, '/'.$path) || (bool) preg_match($regex, $request->getRequestUri());
    }

    protected function resolvedTarget(SeoRedirect $redirect, Request $request): string
    {
        if (! $redirect->is_regex) {
            return $redirect->new_url;
        }

        $regex = $this->normalizeRegex($redirect->old_url);

        if ($regex === null) {
            return $redirect->new_url;
        }

        return preg_replace($regex, $redirect->new_url, trim($request->path(), '/')) ?: $redirect->new_url;
    }

    protected function normalizeRegex(string $pattern): ?string
    {
        if (@preg_match($pattern, '') !== false) {
            return $pattern;
        }

        $wrapped = '#'.$pattern.'#u';

        return @preg_match($wrapped, '') !== false ? $wrapped : null;
    }

    protected function targetUrl(string $target, Request $request): string
    {
        if (! config('lazy-seo.redirects.preserve_query', true) || $request->getQueryString() === null) {
            return $target;
        }

        return $target.(str_contains($target, '?') ? '&' : '?').$request->getQueryString();
    }

    protected function isRedirectLoop(string $target, Request $request): bool
    {
        $targetPath = SeoRedirect::normalizePath((string) (parse_url($target, PHP_URL_PATH) ?: $target));

        return $targetPath === SeoRedirect::normalizePath($request->path());
    }

    protected function markHit(SeoRedirect $redirect): void
    {
        $redirect->newQuery()
            ->whereKey($redirect->getKey())
            ->update([
                'hits' => DB::raw('hits + 1'),
                'last_hit_at' => now(),
            ]);
    }
}
