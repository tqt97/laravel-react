import { useTranslation } from '@/hooks/use-translation';

export function SkipLink() {
    const { t } = useTranslation();

    return (
        <a
            href="#main-content"
            className="focus:bg-background focus:text-foreground focus:ring-ring sr-only fixed top-2 left-2 z-[100] rounded-md px-4 py-2 text-sm font-medium shadow-md focus:not-sr-only focus:ring-2 focus:outline-none"
        >
            {t('Skip to main content')}
        </a>
    );
}
