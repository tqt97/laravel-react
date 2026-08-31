<?php

namespace App\Http\Middleware;

use App\Support\Locale\LocaleResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

final class SetAuthenticatedLocale
{
    public function __construct(private readonly LocaleResolver $resolver) {}

    /** @param Closure(Request): (Response) $next */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolver->resolve($request);

        App::setLocale($locale->value);
        $request->session()->put('locale', $locale->value);
        Cookie::queue(config('locale.cookie.name'), $locale->value, config('locale.cookie.minutes'));

        return $next($request);
    }
}
