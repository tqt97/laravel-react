<?php

namespace App\Actions\Settings;

use App\Enums\Locale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UpdateLocale
{
    public function execute(User $user, string $locale): void
    {
        $locale = Locale::tryFrom($locale)->value ?? Locale::ENGLISH->value;

        DB::transaction(static function () use ($user, $locale): void {
            $user->forceFill(['locale' => $locale])->save();
        });
    }
}
