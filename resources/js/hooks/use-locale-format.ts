import { useTranslation } from '@/hooks/use-translation';

export function useLocaleFormat() {
    const { locale } = useTranslation();

    return {
        formatDate: (
            value: Date | string,
            options?: Intl.DateTimeFormatOptions,
        ) => new Intl.DateTimeFormat(locale, options).format(new Date(value)),
        formatNumber: (value: number, options?: Intl.NumberFormatOptions) =>
            new Intl.NumberFormat(locale, options).format(value),
        formatCurrency: (value: number, currency = 'VND') =>
            new Intl.NumberFormat(locale, {
                style: 'currency',
                currency,
            }).format(value),
    };
}
