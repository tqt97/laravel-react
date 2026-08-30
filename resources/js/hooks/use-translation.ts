import { usePage } from '@inertiajs/react';
import { DEFAULT_LOCALE, type Locale } from '@/types/locale';
type TranslationProps = {
    locale?: Locale;
    translations?: Record<string, string>;
};

export function useTranslation() {
    const { locale = DEFAULT_LOCALE, translations = {} } =
        usePage<TranslationProps>().props;
    const t = (key: string, fallback?: string): string =>
        translations[key] ?? fallback ?? key;

    return { locale, t };
}
