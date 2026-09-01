<?php

namespace App\Http\Middleware;

use App\Enums\Locale;
use App\Support\Locale\LocaleRegistry;
use App\Support\Locale\TimezoneResolver;
use App\Support\Translations\TranslationLoader;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        private readonly TimezoneResolver $timezoneResolver,
        private readonly LocaleRegistry $localeRegistry,
    ) {}

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'locale' => app()->getLocale(),
            'supportedLocales' => $this->localeRegistry->all(),
            'timezone' => $this->timezoneResolver->resolve($request->user()?->timezone),
            'translations' => fn (): array => app(TranslationLoader::class)->load(
                Locale::fromValueOrDefault(app()->getLocale()),
            ),
        ];
    }
}
