<?php

namespace App\Enums;

enum Locale: string
{
    case ENGLISH = 'en';
    case VIETNAMESE = 'vi';

    public function label(): string
    {
        return match ($this) {
            self::ENGLISH => 'English',
            self::VIETNAMESE => 'Tiếng Việt',
        };
    }

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $locale): string => $locale->value,
            self::cases(),
        );
    }
}
