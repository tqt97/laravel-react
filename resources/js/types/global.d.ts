import type { Auth } from '@/types/auth';
import type { Locale } from '@/types/locale';

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
            timezone: string;
            translations: Record<string, string>;
            [key: string]: unknown;
        };
    }
}
