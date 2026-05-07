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

        return redirect()->to($this->targetUrl($redirect->new_url, $request), $redirect->status_code);
    }

    protected function findRedirect(Request $request): ?SeoRedirect
    {
        $path = trim($request->path(), '/');
        $variants = [$path, '/'.$path, $request->getRequestUri(), $request->fullUrl()];

        $redirect = SeoRedirect::query()
            ->enabled()
            ->whereIn('status_code', config('lazy-seo.redirects.allowed_status_codes', [301, 302, 307, 308, 410]))
            ->whereIn('old_url', array_unique($variants))
            ->orderByDesc('id')
            ->first();

        if ($redirect) {
            return $redirect;
        }

        return SeoRedirect::query()
            ->enabled()
            ->whereIn('status_code', config('lazy-seo.redirects.allowed_status_codes', [301, 302, 307, 308, 410]))
            ->where('old_url', 'like', '%*%')
            ->orderByDesc('id')
            ->get()
            ->first(fn (SeoRedirect $item): bool => $this->wildcardMatches($item->old_url, $path));
    }

    protected function wildcardMatches(string $pattern, string $path): bool
    {
        $pattern = trim($pattern, '/');
        $regex = '#^'.str_replace('\\*', '.*', preg_quote($pattern, '#')).'$#u';

        return (bool) preg_match($regex, $path);
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
