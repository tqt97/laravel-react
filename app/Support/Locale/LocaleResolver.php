<?php

namespace App\Support\Locale;

use App\Enums\Locale;
use App\Models\User;
use Illuminate\Http\Request;

final class LocaleResolver
{
    public function __construct(private readonly LocaleRegistry $registry) {}

    public function resolve(Request $request): Locale
    {
        $user = $request->user();
        $sessionLocale = $request->hasSession()
            ? $request->session()->get('locale')
            : null;

        // A saved user preference is authoritative. Session and cookie are
        // temporary preferences, while Accept-Language is only a first-visit
        // hint and must never override an explicit application preference.
        $candidates = [
            $user instanceof User ? $user->locale : null,
            $sessionLocale,
            $request->cookie(config('locale.cookie.name')),
        ];

        foreach ($candidates as $candidate) {
            $locale = $this->registry->fromValue($candidate);

            if ($locale !== null) {
                return $locale;
            }
        }

        if (config('locale.accept_language', true)) {
            // Request::getLanguages() is already ordered by the browser's
            // quality values; stop at the first supported language.
            foreach ($request->getLanguages() as $language) {
                $locale = $this->registry->fromLanguageTag($language);

                if ($locale !== null) {
                    return $locale;
                }
            }
        }

        return $this->registry->default();
    }
}
