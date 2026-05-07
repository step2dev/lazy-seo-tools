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

        if ($redirect->status_code === 410) {
            abort(410, 'Gone');
        }

        if (! $redirect->new_url) {
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
            ->whereIn('old_url', array_unique($variants))
            ->orderByDesc('id')
            ->first();

        if ($redirect) {
            return $redirect;
        }

        return SeoRedirect::query()
            ->enabled()
            ->where('old_url', 'like', '%*%')
            ->get()
            ->first(fn (SeoRedirect $item) => $this->wildcardMatches($item->old_url, $path));
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
}
