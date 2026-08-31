<?php

namespace Tests\Feature;

use App\Enums\Locale;
use App\Support\Translations\TranslationLoader;
use Tests\TestCase;

class TranslationLoaderTest extends TestCase
{
    public function test_frontend_translations_are_loaded_from_php_source(): void
    {
        $translations = app(TranslationLoader::class)->load(Locale::VIETNAMESE);

        $this->assertSame('Đăng nhập', $translations['auth.login']);
        $this->assertSame('Menu điều hướng', $translations['common.navigation_menu']);
        $this->assertSame('Không có quyền truy cập', $translations['errors.403.title']);
        $this->assertSame('Đăng nhập', $translations['frontend.auth.login']);
    }
}
