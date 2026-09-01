<?php

namespace App\Console\Commands;

use App\Enums\Locale;
use App\Support\Locale\LocaleRegistry;
use App\Support\Translations\TranslationLoader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class GenerateTranslationTypes extends Command
{
    protected $signature = 'translations:generate-types {--check : Fail when the generated file is out of date}';

    protected $description = 'Generate TypeScript locale and translation key types';

    public function handle(LocaleRegistry $registry, TranslationLoader $loader): int
    {
        $localeValues = array_map(
            static fn (array $locale): string => $locale['value'],
            $registry->all(),
        );
        $translationKeys = array_keys($loader->load(
            Locale::fromValueOrDefault(config('app.locale')),
        ));
        $translationKeys = array_values(array_unique([
            ...$translationKeys,
            ...array_map(
                static fn (string $key): string => (string) preg_replace('/\.(?:zero|one|two|few|many|other)$/', '', $key),
                array_filter(
                    $translationKeys,
                    static fn (string $key): bool => preg_match('/\.(?:zero|one|two|few|many|other)$/', $key) === 1,
                ),
            ),
        ]));
        sort($localeValues);
        sort($translationKeys);

        $content = $this->render($localeValues, $translationKeys);
        $path = resource_path('js/types/generated-locale.ts');

        if ($this->option('check')) {
            if (! File::exists($path) || File::get($path) !== $content) {
                $this->error('Generated locale types are out of date. Run php artisan translations:generate-types.');

                return self::FAILURE;
            }

            $this->info('Generated locale types are up to date.');

            return self::SUCCESS;
        }

        File::put($path, $content);
        $this->info('Generated '.$path);

        return self::SUCCESS;
    }

    /**
     * @param  array<int, string>  $localeValues
     * @param  array<int, string>  $translationKeys
     */
    private function render(array $localeValues, array $translationKeys): string
    {
        return "// This file is generated. Do not edit it directly.\n\n"
            .'export type Locale = '.implode(' | ', array_map($this->quote(...), $localeValues)).";\n\n"
            ."export type TranslationKey =\n"
            .implode("\n", array_map(
                fn (string $key): string => '    | '.$this->quote($key),
                $translationKeys,
            )).";\n";
    }

    private function quote(string $value): string
    {
        if (str_contains($value, "'")) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }

        return "'".str_replace(
            ['\\', "'"],
            ['\\\\', "\\'"],
            $value,
        )."'";
    }
}
