import type { ReactNode } from 'react';

export function AdminListToolbar({
    filters,
    actions,
}: {
    filters?: ReactNode;
    actions?: ReactNode;
}) {
    return (
        <div className="bg-card flex flex-col gap-3 rounded-xl border p-3 shadow-sm sm:p-4">
            {filters && <div className="min-w-0">{filters}</div>}
            {actions && (
                <div className="flex min-w-0 flex-wrap items-center gap-2">
                    {actions}
                </div>
            )}
        </div>
    );
}
