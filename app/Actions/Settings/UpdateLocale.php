<?php

namespace App\Actions\Settings;

use App\Enums\Locale;
use App\Models\User;

final class UpdateLocale
{
    public function execute(User $user, Locale $locale): void
    {
        if ($user->locale === $locale) {
            return;
        }

        $user->update(['locale' => $locale]);
    }
}
