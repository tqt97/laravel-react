import { usePage } from '@inertiajs/react';
import type { Locale } from '@/types/locale';
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
    const { locale, translations } = usePage<TranslationProps>().props;
    const t = (
        key: string,
        paramsOrFallback?: TranslationParams | string,
        fallback?: string,
    ): string => {
        const params =
            typeof paramsOrFallback === 'string' ? {} : paramsOrFallback;
        const value =
            translations[key] ??
            (typeof paramsOrFallback === 'string'
                ? paramsOrFallback
                : fallback) ??
            key;
        if (import.meta.env.DEV && !translations[key])
            console.warn(`[i18n] Missing translation: ${key}`);
        return interpolate(value, params);
    };

    const tc = (
        key: string,
        count: number,
        params: TranslationParams = {},
    ): string =>
        t(
            `${key}.${count === 1 ? 'one' : 'other'}`,
            { count, ...params },
            t(key, { count, ...params }),
        );

    return { locale, t, tc };
}
