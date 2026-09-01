<?php

namespace App\Support\Locale;

use App\Enums\Locale;

final class LocaleRegistry
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function all(): array
    {
        return array_map(
            static fn (Locale $locale): array => [
                'value' => $locale->value,
                'label' => $locale->label(),
            ],
            Locale::cases(),
        );
    }

    public function fromValue(mixed $value): ?Locale
    {
        if ($value instanceof Locale) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        foreach (Locale::cases() as $locale) {
            if (strcasecmp($locale->value, $value) === 0) {
                return $locale;
            }
        }

        return null;
    }

    public function fromLanguageTag(string $languageTag): ?Locale
    {
        // Prefer an exact regional locale. The base-language fallback keeps
        // vi-VN usable when only vi is registered; enum order is the
        // deterministic tie-breaker if multiple regional variants exist.
        $normalizedTag = str_replace('-', '_', trim($languageTag));
        $locale = $this->fromValue($normalizedTag);

        if ($locale !== null) {
            return $locale;
        }

        $language = strtolower(explode('_', $normalizedTag, 2)[0]);

        foreach (Locale::cases() as $supportedLocale) {
            $supportedLanguage = strtolower(explode(
                '_',
                str_replace('-', '_', $supportedLocale->value),
                2,
            )[0]);

            if ($supportedLanguage === $language) {
                return $supportedLocale;
            }
        }

        return null;
    }

    public function default(): Locale
    {
        return $this->fromValue(config('app.fallback_locale')) ?? Locale::default();
    }
}
