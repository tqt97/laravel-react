<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the user's preferred locale
        $candidate = $request->session()->get('locale')
            ?? $request->cookie(config('locale.cookie.name'))
            ?? config('app.fallback_locale', Locale::default()->value);

        // Set the locale based on the user's preferred locale
        $locale = Locale::fromValueOrDefault($candidate)->value;

        App::setLocale($locale);
        Cookie::queue(config('locale.cookie.name'), $locale, config('locale.cookie.minutes'));

        // Store the locale in the session if it has changed
        if ($request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}
