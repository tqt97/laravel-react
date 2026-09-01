import { useVirtualizer } from '@tanstack/react-virtual';
import { useRef } from 'react';
import type { CSSProperties } from 'react';
import type {
    DataTableColumn,
    SortDirection,
} from '@/components/admin/data-table';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';

/**
 * Opt-in table for genuinely large client-side datasets. Prefer server
 * pagination for normal admin lists; this component only mounts visible rows.
 */
export function VirtualizedDataTable<T>({
    rows,
    columns,
    rowKey,
    sortKey,
    sortDirection = 'asc',
    onSortChange,
    height = 560,
    ariaLabel,
}: {
    rows: T[];
    columns: DataTableColumn<T>[];
    rowKey: (row: T) => string | number;
    sortKey?: string;
    sortDirection?: SortDirection;
    onSortChange?: (key: string, direction: SortDirection) => void;
    height?: number;
    ariaLabel?: string;
}) {
    const { t } = useTranslation();
    const parentRef = useRef<HTMLDivElement>(null);
    // TanStack Virtual owns this imperative measurement object.
    // eslint-disable-next-line react-hooks/incompatible-library
    const virtualizer = useVirtualizer({
        count: rows.length,
        getScrollElement: () => parentRef.current,
        estimateSize: () => 56,
        overscan: 8,
    });

    const requestSort = (column: DataTableColumn<T>) => {
        if (!column.sortable || !onSortChange) {
            return;
        }

        onSortChange(
            column.key,
            sortKey === column.key && sortDirection === 'asc' ? 'desc' : 'asc',
        );
    };

    return (
        <div
            className="bg-card overflow-hidden rounded-xl border shadow-sm"
            role="table"
            aria-label={ariaLabel}
            aria-rowcount={rows.length}
        >
            <div
                className="bg-muted/45 text-muted-foreground grid grid-cols-[repeat(var(--columns),minmax(0,1fr))] px-4 py-3 text-xs font-semibold tracking-wide uppercase"
                style={{ '--columns': columns.length } as CSSProperties}
                role="row"
            >
                {columns.map((column) => (
                    <button
                        key={column.key}
                        type="button"
                        className={cn('text-left', column.headerClassName)}
                        disabled={!column.sortable || !onSortChange}
                        role="columnheader"
                        aria-sort={
                            sortKey !== column.key
                                ? 'none'
                                : sortDirection === 'asc'
                                  ? 'ascending'
                                  : 'descending'
                        }
                        onClick={() => requestSort(column)}
                    >
                        {column.header}
                    </button>
                ))}
            </div>
            <div
                ref={parentRef}
                className="overflow-auto"
                style={{ height }}
                role="rowgroup"
            >
                {rows.length === 0 ? (
                    <p className="text-muted-foreground p-12 text-center text-sm">
                        {t('No data.', 'No data.')}
                    </p>
                ) : (
                    <div
                        style={{
                            height: virtualizer.getTotalSize(),
                            position: 'relative',
                        }}
                    >
                        {virtualizer.getVirtualItems().map((item) => {
                            const row = rows[item.index];

                            return (
                                <div
                                    key={rowKey(row)}
                                    className="hover:bg-muted/25 absolute right-0 left-0 grid grid-cols-[repeat(var(--columns),minmax(0,1fr))] items-center border-t px-4 text-sm"
                                    style={
                                        {
                                            '--columns': columns.length,
                                            height: item.size,
                                            transform: `translateY(${item.start}px)`,
                                        } as CSSProperties
                                    }
                                    role="row"
                                >
                                    {columns.map((column) => (
                                        <div
                                            key={column.key}
                                            className={cn(
                                                'min-w-0 px-1',
                                                column.className,
                                            )}
                                            role="cell"
                                        >
                                            {column.cell(row)}
                                        </div>
                                    ))}
                                </div>
                            );
                        })}
                    </div>
                )}
            </div>
        </div>
    );
}
