<?php

namespace Step2dev\LazySeoTools\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHeadless
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->expectsJson()) {
            abort(403, 'This route is headless-only (JSON API)');
        }

        return $next($request);
    }
}
