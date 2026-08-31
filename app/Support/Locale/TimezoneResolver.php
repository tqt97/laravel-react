<?php

namespace App\Support\Locale;

final class TimezoneResolver
{
    private const FALLBACK_TIMEZONE = 'UTC';

    public function resolve(?string $timezone): string
    {
        if ($this->isValid($timezone)) {
            return $timezone;
        }

        $applicationTimezone = (string) config('app.timezone', self::FALLBACK_TIMEZONE);

        return $this->isValid($applicationTimezone)
            ? $applicationTimezone
            : self::FALLBACK_TIMEZONE;
    }

    private function isValid(?string $timezone): bool
    {
        return $timezone !== null
            && in_array($timezone, timezone_identifiers_list(), true);
    }
}
