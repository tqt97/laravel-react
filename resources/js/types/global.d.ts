import type { Auth } from '@/types/auth';
import type { Locale, SupportedLocale, TranslationKey } from '@/types/locale';

declare module 'react' {
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            locale: Locale;
            supportedLocales: SupportedLocale[];
            timezone: string;
            translations: Record<TranslationKey, string>;
            [key: string]: unknown;
        };
    }
}
