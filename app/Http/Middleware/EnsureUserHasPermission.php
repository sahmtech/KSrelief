<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Example permission middleware wrapper.
 *
 * Prefer route middleware alias `permission:name` (Spatie) for most cases.
 * Use this class when additional logic is required before the permission check.
 */
class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()?->can($permission)) {
            abort(403, __('You do not have permission to perform this action.'));
        }

        return $next($request);
    }
}
