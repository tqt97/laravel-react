<?php

namespace App\Support\Locale;

use App\Enums\Locale;
use App\Models\User;
use Illuminate\Http\Request;

final class LocaleResolver
{
    public function resolve(Request $request): Locale
    {
        $user = $request->user();
        $sessionLocale = $request->hasSession()
            ? $request->session()->get('locale')
            : null;
        $candidate = ($user instanceof User ? $user->locale?->value : null)
            ?? $sessionLocale
            ?? $request->cookie(config('locale.cookie.name'))
            ?? $this->browserLocale($request)
            ?? config('app.fallback_locale', Locale::default()->value);

        return Locale::tryFrom((string) $candidate) ?? $this->fallbackLocale();
    }

    private function browserLocale(Request $request): ?string
    {
        if (! config('locale.accept_language', true)) {
            return null;
        }

        foreach ($request->getLanguages() as $language) {
            $short = strtolower(substr(str_replace('_', '-', $language), 0, 2));
            if (Locale::tryFrom($short)) {
                return $short;
            }
        }

        return null;
    }

    private function fallbackLocale(): Locale
    {
        return Locale::tryFrom((string) config('app.fallback_locale', Locale::default()->value))
            ?? Locale::default();
    }
}
