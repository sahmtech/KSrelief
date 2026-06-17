<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Example role middleware wrapper.
 *
 * Prefer route middleware alias `role:name` (Spatie) for most cases.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()?->hasAnyRole($roles)) {
            abort(403, __('You do not have the required role to access this area.'));
        }

        return $next($request);
    }
}
