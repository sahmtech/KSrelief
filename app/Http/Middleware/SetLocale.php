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
        $locale = config('admin.show_locale_switcher', true)
            ? session('locale', config('app.locale'))
            : config('app.locale');

        if (! in_array($locale, $this->supported, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
