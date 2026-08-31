import { Head, Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';

export default function ErrorPage() {
    const { status } = usePage<{ status: number }>().props;
    const { t } = useTranslation();
    const key = `errors.${status}`;

    return (
        <main className="flex min-h-screen items-center justify-center p-6 text-center">
            <Head title={t(`${key}.title`, t('errors.page_title'))} />
            <section>
                <p className="text-6xl font-bold">{status}</p>
                <h1 className="mt-4 text-2xl font-semibold">
                    {t(`${key}.title`)}
                </h1>
                <p className="text-muted-foreground mt-2">
                    {t(`${key}.description`)}
                </p>
                <Button asChild className="mt-6">
                    <Link href="/">{t('errors.back')}</Link>
                </Button>
            </section>
        </main>
    );
}
