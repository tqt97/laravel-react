<?php

namespace Tests\Unit\Support\Locale;

use App\Enums\Locale;
use App\Support\Locale\LocaleRegistry;
use Tests\TestCase;

class LocaleRegistryTest extends TestCase
{
    public function test_registry_exposes_enum_cases_for_frontend_options(): void
    {
        $registry = app(LocaleRegistry::class);

        $this->assertSame([
            ['value' => 'en', 'label' => 'English'],
            ['value' => 'vi', 'label' => 'Tiếng Việt'],
        ], $registry->all());
    }

    public function test_registry_resolves_exact_and_regional_language_tags(): void
    {
        $registry = app(LocaleRegistry::class);

        $this->assertSame(Locale::VIETNAMESE, $registry->fromValue('VI'));
        $this->assertSame(Locale::VIETNAMESE, $registry->fromLanguageTag('vi-VN'));
        $this->assertSame(Locale::ENGLISH, $registry->fromLanguageTag('en-US'));
    }

    public function test_registry_rejects_invalid_values_and_uses_configured_default(): void
    {
        config(['app.fallback_locale' => Locale::VIETNAMESE->value]);
        $registry = app(LocaleRegistry::class);

        $this->assertNull($registry->fromValue(['vi']));
        $this->assertNull($registry->fromLanguageTag('fr-FR'));
        $this->assertSame(Locale::VIETNAMESE, $registry->default());
    }
}
