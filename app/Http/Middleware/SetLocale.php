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
        $locale = $request->user()->locale->value
            ?? $request->session()->get('locale')
            ?? config('app.locale', Locale::ENGLISH->value);

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}
