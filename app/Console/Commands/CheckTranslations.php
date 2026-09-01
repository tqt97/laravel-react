<?php

namespace App\Console\Commands;

use App\Enums\Locale;
use App\Support\Translations\TranslationLoader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class CheckTranslations extends Command
{
    protected $signature = 'translations:check {--locale= : Compare one locale against the default}';

    protected $description = 'Check translation key parity across supported locales';

    public function handle(TranslationLoader $loader): int
    {
        $default = Locale::fromValueOrDefault(config('app.locale'));
        $failed = false;
        $requestedLocale = $this->option('locale');

        if ($requestedLocale !== null && Locale::tryFrom($requestedLocale) === null) {
            $this->error("Unsupported locale: {$requestedLocale}");

            return self::FAILURE;
        }

        foreach (Locale::cases() as $locale) {
            if ($locale === $default || ($requestedLocale !== null && $locale->value !== $requestedLocale)) {
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

        try {
            $catalogs = [];

            foreach (Locale::cases() as $locale) {
                $catalogs[$locale->value] = $loader->load($locale);
            }

            // Keep the usage scan inside the same boundary as catalog loading:
            // a default-locale collision must be reported as command failure.
            $failed = $this->checkFrontendUsage(
                $catalogs[$default->value],
            ) || $failed;
        } catch (\LogicException $exception) {
            $this->error($exception->getMessage());
            $failed = true;
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
     * @param  array<string, string>  $catalog
     */
    private function checkFrontendUsage(array $catalog): bool
    {
        $missing = [];

        foreach (File::allFiles(resource_path('js')) as $file) {
            if (! in_array($file->getExtension(), ['ts', 'tsx'], true)) {
                continue;
            }

            $contents = File::get($file->getPathname());
            preg_match_all(
                '/\b(?:t|tc)\s*\(\s*([\'\"])(.*?)\1/s',
                $contents,
                $matches,
            );

            foreach (array_unique($matches[2]) as $key) {
                if (! array_key_exists($key, $catalog)) {
                    $missing[$key][] = $file->getPathname();
                }
            }
        }

        foreach ($missing as $key => $files) {
            $this->error(sprintf(
                'frontend: missing %s (%s)',
                $key,
                implode(', ', array_map(fn (string $path): string => str_replace(base_path().'/', '', $path), $files)),
            ));
        }

        return $missing !== [];
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
