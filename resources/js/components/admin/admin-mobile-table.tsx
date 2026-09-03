import type { ReactNode } from 'react';
import { AdminTableCard } from '@/components/admin/admin-table-card';
import type { DataTableColumn } from '@/components/admin/data-table';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';

/**
 * Card representation for dense tables on small screens. Keep the same
 * columns as DataTable so mobile and desktop never drift from one another.
 */
export function AdminMobileTable<T>({
    rows,
    columns,
    rowKey,
    emptyMessage,
}: {
    rows: T[];
    columns: DataTableColumn<T>[];
    rowKey: (row: T) => string | number;
    emptyMessage?: ReactNode;
}) {
    const { t } = useTranslation();

    if (rows.length === 0) {
        return (
            <p className="text-muted-foreground px-2 py-8 text-center text-sm">
                {emptyMessage ?? t('No data.', 'No data.')}
            </p>
        );
    }

    return (
        <div className="space-y-3" role="list">
            {rows.map((row) => (
                <AdminTableCard key={rowKey(row)}>
                    <dl>
                        {columns.map((column) =>
                            column.mobileHidden ? null : (
                                <div
                                    key={column.key}
                                    className={cn(
                                        'grid grid-cols-[minmax(7rem,35%)_minmax(0,1fr)] gap-3 border-b py-2 last:border-b-0',
                                        column.mobileClassName,
                                    )}
                                >
                                    <div className="text-muted-foreground text-xs font-semibold">
                                        {column.mobileLabel ?? column.header}
                                    </div>
                                    <div className="min-w-0 break-words">
                                        {column.cell(row)}
                                    </div>
                                </div>
                            ),
                        )}
                    </dl>
                </AdminTableCard>
            ))}
        </div>
    );
}
