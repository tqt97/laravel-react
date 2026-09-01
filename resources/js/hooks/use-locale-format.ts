import { useTranslation } from '@/hooks/use-translation';
import { usePage } from '@inertiajs/react';

export function useLocaleFormat() {
    const { locale } = useTranslation();
    const requestedTimezone = String(usePage().props.timezone ?? 'UTC');
    const timezone = (() => {
        try {
            new Intl.DateTimeFormat(locale, {
                timeZone: requestedTimezone,
            });

            return requestedTimezone;
        } catch {
            return 'UTC';
        }
    })();

    return {
        formatDate: (
            value: Date | string,
            options?: Intl.DateTimeFormatOptions,
        ) =>
            new Intl.DateTimeFormat(locale, {
                ...options,
                timeZone: timezone,
            }).format(new Date(value)),
        formatNumber: (value: number, options?: Intl.NumberFormatOptions) =>
            new Intl.NumberFormat(locale, options).format(value),
        formatCurrency: (value: number, currency = 'VND') =>
            new Intl.NumberFormat(locale, {
                style: 'currency',
                currency,
            }).format(value),
    };
}
