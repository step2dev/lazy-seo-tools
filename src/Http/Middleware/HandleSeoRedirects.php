<?php

namespace Step2dev\LazySeoTools\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Step2dev\LazySeoTools\Models\SeoRedirect;

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
        $path = trim($request->path(), '/');
        $variants = array_unique([$path, '/'.$path, $request->getRequestUri(), $request->fullUrl()]);

        $query = SeoRedirect::query()
            ->enabled()
            ->whereIn('status_code', config('lazy-seo.redirects.allowed_status_codes', [301, 302, 307, 308, 410]));

        $redirect = (clone $query)
            ->where('is_regex', false)
            ->whereIn('old_url', $variants)
            ->orderByDesc('id')
            ->first();

        if ($redirect) {
            return $redirect;
        }

        if (config('lazy-seo.redirects.wildcard_enabled', true)) {
            $redirect = (clone $query)
                ->where('is_regex', false)
                ->where('old_url', 'like', '%*%')
                ->orderByDesc('id')
                ->get()
                ->first(fn (SeoRedirect $item): bool => $this->wildcardMatches($item->old_url, $path));

            if ($redirect) {
                return $redirect;
            }
        }

        if (! config('lazy-seo.redirects.regex_enabled', true)) {
            return null;
        }

        return (clone $query)
            ->where('is_regex', true)
            ->orderByDesc('id')
            ->get()
            ->first(fn (SeoRedirect $item): bool => $this->regexMatches($item->old_url, $path, $request));
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
        $targetPath = trim(parse_url($target, PHP_URL_PATH) ?: $target, '/');

        return $targetPath === trim($request->path(), '/');
    }

    protected function markHit(SeoRedirect $redirect): void
    {
        $redirect->newQuery()
            ->whereKey($redirect->getKey())
            ->update([
                'hits' => $redirect->hits + 1,
                'last_hit_at' => now(),
            ]);
    }
}
