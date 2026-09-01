import { usePage } from '@inertiajs/react';
import type { Locale, TranslationKey } from '@/types/locale';
type TranslationProps = {
    locale?: Locale;
    translations?: Record<string, string>;
};

type TranslationParams = Record<string, string | number>;

function interpolate(value: string, params: TranslationParams = {}): string {
    return value.replace(/:([a-zA-Z0-9_]+)/g, (_, key: string) =>
        params[key] === undefined ? `:${key}` : String(params[key]),
    );
}

export function useTranslation() {
    const { locale, translations: pageTranslations } =
        usePage<TranslationProps>().props;
    const translations: Record<string, string> = pageTranslations ?? {};

    const resolve = (key: string): string | undefined => translations[key];

    const translate = (
        key: string,
        paramsOrFallback?: TranslationParams | string,
        fallback?: string,
    ): string => {
        const params =
            typeof paramsOrFallback === 'string' ? {} : paramsOrFallback;
        const value =
            resolve(key) ??
            (typeof paramsOrFallback === 'string'
                ? paramsOrFallback
                : fallback) ??
            key;
        if (import.meta.env.DEV && resolve(key) === undefined)
            console.warn(`[i18n] Missing translation: ${key}`);
        return interpolate(value, params);
    };

    const t = (
        key: TranslationKey,
        paramsOrFallback?: TranslationParams | string,
        fallback?: string,
    ): string => translate(key, paramsOrFallback, fallback);

    // Use td only when a key is assembled at runtime. Static calls should use
    // t so generated TranslationKey types can catch missing keys at build time.
    const td = (
        key: string,
        paramsOrFallback?: TranslationParams | string,
        fallback?: string,
    ): string => translate(key, paramsOrFallback, fallback);

    const tc = (
        key: TranslationKey,
        count: number,
        params: TranslationParams = {},
    ): string => {
        const pluralCategory = new Intl.PluralRules(locale ?? 'en').select(
            count,
        );
        const pluralKey = `${key}.${pluralCategory}`;
        const pluralValue = resolve(pluralKey);

        if (pluralValue !== undefined) {
            return interpolate(pluralValue, { count, ...params });
        }

        return td(key, { count, ...params });
    };

    return { locale, t, td, tc };
}
