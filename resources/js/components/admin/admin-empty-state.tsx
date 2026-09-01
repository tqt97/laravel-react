import type { ReactNode } from 'react';
import { useTranslation } from '@/hooks/use-translation';

export function AdminEmptyState({
    title,
    description,
    action,
}: {
    title?: string;
    description?: string;
    action?: ReactNode;
}) {
    const { t } = useTranslation();

    return (
        <div className="rounded-xl border border-dashed p-10 text-center">
            <h2 className="font-semibold">{title ?? t('No data.')}</h2>
            {description && (
                <p className="text-muted-foreground mt-1 text-sm">
                    {description}
                </p>
            )}
            {action && <div className="mt-4">{action}</div>}
        </div>
    );
}
