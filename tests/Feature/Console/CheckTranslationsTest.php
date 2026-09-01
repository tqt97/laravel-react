<?php

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CheckTranslationsTest extends TestCase
{
    public function test_generated_translation_types_are_current(): void
    {
        $this->assertSame(0, Artisan::call('translations:generate-types', ['--check' => true]));
    }

    public function test_translation_check_rejects_a_missing_react_translation_key(): void
    {
        $path = resource_path('js/translation-check-test.ts');
        File::put($path, "export const missing = t('testing.missing_key');");

        try {
            $this->assertSame(1, Artisan::call('translations:check'));
            $this->assertStringContainsString(
                'frontend: missing testing.missing_key',
                Artisan::output(),
            );
        } finally {
            File::delete($path);
        }
    }
}
