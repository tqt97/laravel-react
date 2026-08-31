<?php

namespace App\Support\Translations;

use App\Enums\Locale;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

final class TranslationLoader
{
    /**
     * @param  array<int, string>|null  $namespaces
     * @return array<string, string>
     */
    public function load(Locale $locale, ?array $namespaces = null): array
    {
        $namespaces ??= config('locale.frontend_namespaces', []);
        sort($namespaces);
        $fingerprint = $this->fingerprint($locale, $namespaces);

        $key = sprintf(
            'translations.%s.%s.%s',
            $locale->value,
            md5(implode('|', $namespaces)),
            $fingerprint,
        );

        return Cache::rememberForever($key, function () use ($locale, $namespaces): array {
            $result = [];
            foreach ($namespaces as $namespace) {
                $path = lang_path($locale->value.'/'.$namespace.'.php');
                if (! File::exists($path)) {
                    continue;
                }

                foreach ($this->flatten(File::getRequire($path)) as $translationKey => $value) {
                    $result[$namespace.'.'.$translationKey] = $value;
                    $result[$translationKey] ??= $value;
                }
            }

            return $result;
        });
    }

    /**
     * @param  array<int, string>  $namespaces
     */
    private function fingerprint(Locale $locale, array $namespaces): string
    {
        $parts = [];

        foreach ($namespaces as $namespace) {
            $path = lang_path($locale->value.'/'.$namespace.'.php');

            if (is_file($path)) {
                $parts[] = $path.':'.filemtime($path).':'.filesize($path);
            }
        }

        return md5(implode('|', $parts));
    }

    /**
     * @param  array<string, mixed>  $translations
     * @return array<string, string>
     */
    private function flatten(array $translations, string $prefix = ''): array
    {
        $result = [];

        foreach ($translations as $key => $value) {
            $translationKey = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value)) {
                $result = [...$result, ...$this->flatten($value, $translationKey)];
            } else {
                $result[$translationKey] = (string) $value;
            }
        }

        return $result;
    }
}
