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

    public static function default(): self
    {
        return self::ENGLISH;
    }

    public static function fromValueOrDefault(mixed $value): self
    {
        return self::tryFrom((string) $value) ?? self::default();
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return array_reduce(self::cases(), static function (array $labels, self $locale): array {
            $labels[$locale->value] = $locale->label();

            return $labels;
        }, []);
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
