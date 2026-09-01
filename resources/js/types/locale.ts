export type { Locale, TranslationKey } from './generated-locale';
import type { Locale } from './generated-locale';

export type SupportedLocale = {
    value: Locale;
    label: string;
    flag: string;
};
