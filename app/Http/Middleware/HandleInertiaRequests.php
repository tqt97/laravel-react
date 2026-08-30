<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
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
            'translations' => fn (): array => Cache::rememberForever(
                'translations.'.app()->getLocale(),
                fn (): array => collect(File::glob(lang_path(app()->getLocale().'/*.json')))
                    ->merge(
                        File::exists(lang_path(app()->getLocale().'.json'))
                            ? [lang_path(app()->getLocale().'.json')]
                            : [],
                    )
                    ->reduce(
                        fn (array $translations, string $path): array => [
                            ...$translations,
                            ...(array) File::json($path),
                        ],
                        [],
                    ),
            ),
        ];
    }
}
