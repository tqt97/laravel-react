import { Head } from '@inertiajs/react';
import AppearanceTabs from '@/components/appearance-tabs';
import Heading from '@/components/heading';
import LanguageSwitcher from '@/components/language-switcher';
import { useTranslation } from '@/hooks/use-translation';
import { edit as editAppearance } from '@/routes/appearance';

export default function Appearance() {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('Appearance')} />
            <h1 className="sr-only">{t('Appearance settings')}</h1>
            <div className="space-y-6">
                <Heading
                    variant="small"
                    title={t('Appearance')}
                    description={t(
                        'Update the appearance settings for your account',
                    )}
                />
                <AppearanceTabs />
                <div className="bg-card rounded-xl border p-4">
                    <Heading
                        variant="small"
                        title={t('Language settings')}
                        description={t(
                            'Choose the language used across the application.',
                        )}
                    />
                    <div className="mt-4">
                        <LanguageSwitcher />
                    </div>
                </div>
            </div>
        </>
    );
}

Appearance.layout = {
    breadcrumbs: [
        {
            title: 'Appearance settings',
            href: editAppearance(),
        },
    ],
};
