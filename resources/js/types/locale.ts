export const LOCALES = {
    ENGLISH: 'en',
    VIETNAMESE: 'vi',
} as const;

export type Locale = (typeof LOCALES)[keyof typeof LOCALES];

export const DEFAULT_LOCALE: Locale = LOCALES.ENGLISH;

export function getNextLocale(locale: Locale): Locale {
    return locale === LOCALES.VIETNAMESE ? LOCALES.ENGLISH : LOCALES.VIETNAMESE;
}
