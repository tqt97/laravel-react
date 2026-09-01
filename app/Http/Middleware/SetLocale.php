<?php

namespace App\Http\Middleware;

use App\Support\Locale\LocaleManager;
use App\Support\Locale\LocaleResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(
        private readonly LocaleResolver $resolver,
        private readonly LocaleManager $manager,
    ) {}

    /**
     * Handle an incoming request.
     *
     * This middleware is appended after StartSession and before validation so
     * Laravel's translated validation messages use the resolved locale. The
     * default session guard can resolve an authenticated user here before the
     * route-level auth middleware runs.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolver->resolve($request);

        App::setLocale($locale->value);
        $this->manager->syncRequest($request, $locale);

        return $next($request);
    }
}
