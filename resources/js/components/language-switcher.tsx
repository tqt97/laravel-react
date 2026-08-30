import { router } from '@inertiajs/react';
import { Languages } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { useState } from 'react';
import { update } from '@/routes/locale';
import { getNextLocale, LOCALES } from '@/types/locale';

export default function LanguageSwitcher() {
    const { locale, t } = useTranslation();
    const nextLocale = getNextLocale(locale);
    const languageLabel =
        locale === LOCALES.VIETNAMESE ? t('Vietnamese') : t('English');

    const [processing, setProcessing] = useState(false);
    const changeLocale = () => {
        setProcessing(true);

        router.patch(
            update.url(),
            { locale: nextLocale },
            {
                preserveScroll: true,
                preserveState: false,
                replace: true,
                onFinish: () => setProcessing(false),
            },
        );
    };
    return (
        <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={changeLocale}
            disabled={processing}
            title={t('Language')}
        >
            <Languages />
            {languageLabel}
        </Button>
    );
}
