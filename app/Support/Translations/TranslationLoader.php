<?php

namespace App\Support\Translations;

use App\Enums\Locale;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

final class TranslationLoader
{
    public function __construct(private readonly string $languagePath = '') {}

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

        return Cache::remember($key, config('locale.translation_cache_ttl', 86400), function () use ($locale, $namespaces): array {
            $result = [];
            foreach ($namespaces as $namespace) {
                $path = $this->path($locale, $namespace);
                if (! File::exists($path)) {
                    continue;
                }

                foreach ($this->flatten(File::getRequire($path)) as $translationKey => $value) {
                    $result[$namespace.'.'.$translationKey] = $value;

                    // Short aliases are kept for the existing React API, but
                    // collisions must fail instead of depending on namespace
                    // sort order and silently returning the wrong translation.
                    if (array_key_exists($translationKey, $result)) {
                        throw new \LogicException(sprintf(
                            'Translation key collision for "%s" between namespaces while loading [%s].',
                            $translationKey,
                            $locale->value,
                        ));
                    }

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
        // The content hash makes edits visible immediately while the TTL
        // bounds stale cache entries created by previous file contents.
        $parts = [];

        foreach ($namespaces as $namespace) {
            $path = $this->path($locale, $namespace);

            if (is_file($path)) {
                $parts[] = $path.':'.(hash_file('xxh128', $path) ?: '');
            } else {
                $parts[] = $path.':missing';
            }
        }

        return md5(implode('|', $parts));
    }

    private function path(Locale $locale, string $namespace): string
    {
        return ($this->languagePath === '' ? lang_path($locale->value) : $this->languagePath.'/'.$locale->value)
            .'/'.$namespace.'.php';
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
