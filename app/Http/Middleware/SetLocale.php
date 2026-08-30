<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
        $candidate = $request->user()?->locale->value
            ?? $request->session()->get('locale')
            ?? config('app.locale', Locale::ENGLISH->value);

        // Set the locale based on the user's preferred locale
        $localeEnum = Locale::tryFrom((string) $candidate);
        $locale = $localeEnum instanceof Locale
            ? $localeEnum->value
            : Locale::ENGLISH->value;

        App::setLocale($locale);

        // Store the locale in the session if it has changed
        if ($request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}
