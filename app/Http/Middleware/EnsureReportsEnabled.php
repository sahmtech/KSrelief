<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureReportsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('admin.show_reports', true)) {
            abort(404);
        }

        return $next($request);
    }
}
