<?php

namespace App\Actions\Settings;

use App\Enums\Locale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateLocale
{
    public function execute(?User $user, string $locale): void
    {
        $localeEnum = Locale::tryFrom($locale);
        $locale = $localeEnum instanceof Locale
            ? $localeEnum->value
            : Locale::ENGLISH->value;

        if ($user === null) {
            return;
        }

        DB::transaction(static function () use ($user, $locale): void {
            $user->forceFill(['locale' => $locale])->save();
        });
    }
}
