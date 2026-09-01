import type { ReactNode } from 'react';
import { useTranslation } from '@/hooks/use-translation';

export function AdminErrorState({
    message,
    action,
}: {
    message?: string;
    action?: ReactNode;
}) {
    const { t } = useTranslation();

    return (
        <div
            role="alert"
            aria-live="assertive"
            className="border-destructive/30 bg-destructive/5 rounded-xl border p-6 text-center"
        >
            <p className="font-medium">
                {message ?? t('Something went wrong.')}
            </p>
            {action && <div className="mt-4">{action}</div>}
        </div>
    );
}
