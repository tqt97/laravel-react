<?php

namespace Tests\Feature;

use App\Enums\Locale;
use App\Support\Translations\TranslationLoader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
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

    public function test_translation_cache_invalidates_when_file_content_changes(): void
    {
        $root = storage_path('framework/testing/translation-cache');
        File::deleteDirectory($root);
        $path = $root.'/en/frontend.php';
        File::ensureDirectoryExists(dirname($path));
        File::put($path, "<?php return ['title' => 'First'];");
        Cache::flush();

        try {
            $loader = new TranslationLoader($root);

            $this->assertSame('First', $loader->load(Locale::ENGLISH)['title']);

            File::put($path, "<?php return ['title' => 'Second'];");

            $this->assertSame('Second', $loader->load(Locale::ENGLISH)['title']);
        } finally {
            File::deleteDirectory($root);
        }
    }

    public function test_translation_namespace_collisions_fail_loudly(): void
    {
        $root = storage_path('framework/testing/translation-collision');
        File::deleteDirectory($root);
        File::ensureDirectoryExists($root.'/en');
        File::put($root.'/en/first.php', "<?php return ['title' => 'First'];");
        File::put($root.'/en/second.php', "<?php return ['title' => 'Second'];");
        Cache::flush();

        try {
            $this->expectException(\LogicException::class);

            new TranslationLoader($root)->load(Locale::ENGLISH, ['first', 'second']);
        } finally {
            File::deleteDirectory($root);
        }
    }
}
