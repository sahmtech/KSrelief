<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /** @var list<string> */
    private array $supported = ['en', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        // Honor session choice for public pages (e.g. /register) even when the
        // admin navbar locale switcher is hidden.
        $locale = session('locale', config('app.locale', 'ar'));

        if (! in_array($locale, $this->supported, true)) {
            $locale = config('app.fallback_locale', 'ar');
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
