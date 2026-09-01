<?php

return [
    'cookie' => [
        'name' => env('LOCALE_COOKIE_NAME', 'app_locale'),
        'minutes' => env('LOCALE_COOKIE_MINUTES', 60 * 24 * 365),
    ],

    'frontend_namespaces' => [
        'frontend',
    ],
    'translation_cache_ttl' => env('LOCALE_TRANSLATION_CACHE_TTL', 86400),
    'accept_language' => env('LOCALE_ACCEPT_LANGUAGE', true),
];
