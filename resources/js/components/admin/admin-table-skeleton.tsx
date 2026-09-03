import { useTranslation } from '@/hooks/use-translation';

export function AdminTableSkeleton({ rows = 5 }: { rows?: number }) {
    const { t } = useTranslation();

    return (
        <div
            className="bg-card space-y-3 rounded-xl border p-4"
            aria-busy="true"
            aria-label={t('Loading…', 'Loading…')}
            role="status"
        >
            {Array.from({ length: rows }, (_, index) => (
                <div
                    key={index}
                    className="bg-muted h-12 animate-pulse rounded-md"
                />
            ))}
        </div>
    );
}
