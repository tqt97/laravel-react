<?php

namespace App\Console\Commands;

use App\Enums\Locale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class CheckTranslations extends Command
{
    protected $signature = 'translations:check {--locale= : Compare one locale against the default}';

    protected $description = 'Check translation key parity across supported locales';

    public function handle(): int
    {
        $default = Locale::fromValueOrDefault(config('app.locale'));
        $failed = false;
        $requestedLocale = $this->option('locale');

        if ($requestedLocale !== null && Locale::tryFrom($requestedLocale) === null) {
            return self::FAILURE;
        }

        foreach (Locale::cases() as $locale) {
            if ($locale === $default || ($this->option('locale') && $locale->value !== $this->option('locale'))) {
                continue;
            }

            $base = $this->keys($default);
            $keys = $this->keys($locale);

            foreach (array_diff($base, $keys) as $key) {
                $this->error("{$locale->value}: missing {$key}");
                $failed = true;
            }

            foreach (array_diff($keys, $base) as $key) {
                $this->warn("{$locale->value}: extra {$key}");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /** @return array<int, string> */
    private function keys(Locale $locale): array
    {
        $keys = [];
        foreach (File::glob(lang_path($locale->value.'/*.php')) as $path) {
            $namespace = basename($path, '.php');
            $translations = File::getRequire($path);

            foreach ($this->flattenKeys($translations, $namespace) as $key) {
                $keys[] = $key;
            }
        }
        sort($keys);

        return $keys;
    }

    /**
     * @param  array<string, mixed>  $translations
     * @return array<int, string>
     */
    private function flattenKeys(array $translations, string $prefix): array
    {
        $keys = [];

        foreach ($translations as $key => $value) {
            $fullKey = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value)) {
                $keys = [...$keys, ...$this->flattenKeys($value, $fullKey)];
            } else {
                $keys[] = $fullKey;
            }
        }

        return $keys;
    }
}
