<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use App\Support\Locale\LocaleResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(private readonly LocaleResolver $resolver) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolver->resolve($request)->value;

        App::setLocale($locale);
        Cookie::queue(config('locale.cookie.name'), $locale, config('locale.cookie.minutes'));

        // Store the locale in the session if it has changed
        if ($request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}
