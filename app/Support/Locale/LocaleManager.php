<?php

namespace App\Support\Locale;

use App\Enums\Locale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

final class LocaleManager
{
    /**
     * Keep the request preference in sync without touching the user's profile.
     * This runs for guests as well as authenticated users, so a temporary
     * browser preference can be used until the user explicitly saves a locale.
     */
    public function syncRequest(Request $request, Locale $locale): void
    {
        if ($request->hasSession() && $request->session()->get('locale') !== $locale->value) {
            $request->session()->put('locale', $locale->value);
        }

        $cookieName = (string) config('locale.cookie.name');

        if ($request->cookie($cookieName) !== $locale->value) {
            Cookie::queue(
                $cookieName,
                $locale->value,
                config('locale.cookie.minutes'),
            );
        }
    }

    public function updateUser(User $user, Locale $locale): void
    {
        if ($user->locale !== $locale) {
            $user->update(['locale' => $locale]);
        }
    }
}
