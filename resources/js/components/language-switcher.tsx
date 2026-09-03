import { router, usePage } from '@inertiajs/react';
import { Languages } from 'lucide-react';
import { useTranslation } from '@/hooks/use-translation';
import { useState } from 'react';
import { toast } from 'sonner';
import { update } from '@/routes/locale';
import type { Locale, SupportedLocale } from '@/types/locale';

export default function LanguageSwitcher() {
    const { locale, t } = useTranslation();
    const supportedLocales =
        usePage<{ supportedLocales?: SupportedLocale[] }>().props
            .supportedLocales ?? [];

    const [processing, setProcessing] = useState(false);
    const changeLocale = (nextLocale: Locale) => {
        if (nextLocale === locale) return;
        setProcessing(true);

        router.patch(
            update.url(),
            { locale: nextLocale },
            {
                preserveScroll: true,
                preserveState: false,
                replace: true,
                onFinish: () => setProcessing(false),
                onError: () => toast.error(t('Unable to update language.')),
            },
        );
    };
    return (
        <label className="inline-flex items-center gap-2">
            <Languages aria-hidden="true" className="size-4" />
            <span className="sr-only">{t('Language')}</span>
            <select
                value={locale}
                onChange={(event) => changeLocale(event.target.value as Locale)}
                disabled={processing}
                aria-label={t('Change language')}
                aria-busy={processing}
                className="bg-background w-22 truncate rounded-md border px-2 py-1.5 text-sm hover:cursor-pointer disabled:cursor-not-allowed sm:w-auto"
            >
                {supportedLocales.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </label>
    );
}
