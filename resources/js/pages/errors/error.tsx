import { Head, Link, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';

export default function ErrorPage() {
    const { status } = usePage<{ status: number }>().props;
    const { t, td } = useTranslation();
    const key = `errors.${status}`;

    return (
        <main className="flex min-h-screen items-center justify-center p-6 text-center">
            <Head title={td(`${key}.title`, t('errors.page_title'))} />
            <section>
                <p className="text-6xl font-bold">{status}</p>
                <h1 className="mt-4 text-2xl font-semibold">
                    {td(`${key}.title`)}
                </h1>
                <p className="text-muted-foreground mt-2">
                    {td(`${key}.description`)}
                </p>
                <Button asChild className="mt-6">
                    <Link href="/">{t('errors.back')}</Link>
                </Button>
            </section>
        </main>
    );
}
