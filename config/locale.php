<?php

use App\Enums\Locale;

return [
    'supported' => array_map(
        static fn (Locale $locale): string => $locale->value,
        Locale::cases(),
    ),

    'cookie' => [
        'name' => env('LOCALE_COOKIE_NAME', 'app_locale'),
        'minutes' => env('LOCALE_COOKIE_MINUTES', 60 * 24 * 365),
    ],

    'frontend_namespaces' => [
        'frontend',
    ],
];
