<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

final class SetAuthenticatedLocale
{
    /** @param Closure(Request): (Response) $next */
    public function handle(Request $request, Closure $next): Response
    {
        $userLocale = $request->user()?->locale;
        $locale = $userLocale instanceof Locale
            ? $userLocale
            : Locale::fromValueOrDefault($request->session()->get('locale'));

        App::setLocale($locale->value);
        $request->session()->put('locale', $locale->value);
        Cookie::queue(config('locale.cookie.name'), $locale->value, config('locale.cookie.minutes'));

        return $next($request);
    }
}
