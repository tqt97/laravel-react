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

    public function flag(): string
    {
        return match ($this) {
            self::ENGLISH => '🇬🇧',
            self::VIETNAMESE => '🇻🇳',
        };
    }

    public static function default(): self
    {
        return self::ENGLISH;
    }

    public static function fromValueOrDefault(mixed $value): self
    {
        return is_string($value)
            ? self::tryFrom($value) ?? self::default()
            : self::default();
    }
}
