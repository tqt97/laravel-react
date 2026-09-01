import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-react';
import type { ReactNode } from 'react';
import { AdminTableCard } from '@/components/admin/admin-table-card';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { useTranslation } from '@/hooks/use-translation';
import { cn } from '@/lib/utils';

export type SortDirection = 'asc' | 'desc';

export type DataTableColumn<T> = {
    key: string;
    header: ReactNode;
    cell: (row: T) => ReactNode;
    sortable?: boolean;
    className?: string;
    headerClassName?: string;
    align?: 'left' | 'center' | 'right';
    mobileHidden?: boolean;
    mobileLabel?: ReactNode;
    mobileClassName?: string;
};

export function DataTable<T>({
    rows,
    columns,
    rowKey,
    sortKey,
    sortDirection = 'asc',
    onSortChange,
    emptyMessage,
    ariaLabel,
    selectable = false,
    selectedKeys = [],
    onSelectionChange,
}: {
    rows: T[];
    columns: DataTableColumn<T>[];
    rowKey: (row: T) => string | number;
    sortKey?: string;
    sortDirection?: SortDirection;
    onSortChange?: (key: string, direction: SortDirection) => void;
    emptyMessage?: string;
    ariaLabel?: string;
    selectable?: boolean;
    selectedKeys?: Array<string | number>;
    onSelectionChange?: (keys: Array<string | number>) => void;
}) {
    const { t } = useTranslation();
    const resolvedEmptyMessage = emptyMessage ?? t('No data.', 'No data.');
    const allVisibleSelected =
        rows.length > 0 &&
        rows.every((row) => selectedKeys.includes(rowKey(row)));
    const toggleRow = (row: T, checked: boolean) => {
        const key = rowKey(row);
        const keys = checked
            ? [...new Set([...selectedKeys, key])]
            : selectedKeys.filter((selectedKey) => selectedKey !== key);
        onSelectionChange?.(keys);
    };
    const requestSort = (column: DataTableColumn<T>) => {
        if (!column.sortable || !onSortChange) {
            return;
        }

        const direction: SortDirection =
            sortKey === column.key && sortDirection === 'asc' ? 'desc' : 'asc';
        onSortChange(column.key, direction);
    };

    return (
        <div
            className="bg-card overflow-hidden rounded-xl border shadow-sm"
            aria-label={ariaLabel}
        >
            <div className="space-y-3 p-3 md:hidden">
                {rows.length === 0 ? (
                    <p className="text-muted-foreground px-2 py-8 text-center text-sm">
                        {resolvedEmptyMessage}
                    </p>
                ) : (
                    rows.map((row) => (
                        <AdminTableCard key={rowKey(row)}>
                            {selectable && (
                                <div className="mb-3 flex items-center gap-2 border-b pb-3">
                                    <Checkbox
                                        checked={selectedKeys.includes(
                                            rowKey(row),
                                        )}
                                        onCheckedChange={(checked) =>
                                            toggleRow(row, Boolean(checked))
                                        }
                                        aria-label={t('Select user')}
                                    />
                                    <span className="text-muted-foreground text-xs">
                                        {t('Select user')}
                                    </span>
                                </div>
                            )}
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
                                            <dt className="text-muted-foreground text-xs font-semibold">
                                                {column.mobileLabel ??
                                                    column.header}
                                            </dt>
                                            <dd
                                                className={cn(
                                                    'min-w-0 wrap-break-word',
                                                    column.align === 'center' &&
                                                        'text-center',
                                                    column.align === 'right' &&
                                                        'text-right',
                                                )}
                                            >
                                                {column.cell(row)}
                                            </dd>
                                        </div>
                                    ),
                                )}
                            </dl>
                        </AdminTableCard>
                    ))
                )}
            </div>
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full min-w-180 text-sm">
                    <thead className="bg-muted/65 text-muted-foreground text-left text-xs font-semibold tracking-wide uppercase">
                        <tr>
                            {selectable && (
                                <th className="w-12 px-4 py-3">
                                    <Checkbox
                                        checked={allVisibleSelected}
                                        onCheckedChange={(checked) => {
                                            const visibleKeys =
                                                rows.map(rowKey);
                                            onSelectionChange?.(
                                                checked
                                                    ? [
                                                          ...new Set([
                                                              ...selectedKeys,
                                                              ...visibleKeys,
                                                          ]),
                                                      ]
                                                    : selectedKeys.filter(
                                                          (key) =>
                                                              !visibleKeys.includes(
                                                                  key,
                                                              ),
                                                      ),
                                            );
                                        }}
                                        aria-label={t('Select all')}
                                    />
                                </th>
                            )}
                            {columns.map((column) => (
                                <th
                                    key={column.key}
                                    className={cn(
                                        'px-4 py-3',
                                        column.align === 'center' &&
                                            'text-center',
                                        column.align === 'right' &&
                                            'text-right',
                                        column.headerClassName,
                                    )}
                                    aria-sort={
                                        column.sortable &&
                                        sortKey === column.key
                                            ? sortDirection === 'asc'
                                                ? 'ascending'
                                                : 'descending'
                                            : undefined
                                    }
                                >
                                    {column.sortable && onSortChange ? (
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            className={cn(
                                                'text-muted-foreground hover:text-foreground h-7 px-2 font-semibold',
                                                column.align === 'left' &&
                                                    '-ml-2',
                                                column.align === 'center' &&
                                                    'mx-auto',
                                                column.align === 'right' &&
                                                    'ml-auto',
                                            )}
                                            onClick={() => requestSort(column)}
                                        >
                                            {column.header}
                                            {sortKey !== column.key ? (
                                                <ArrowUpDown
                                                    aria-hidden="true"
                                                    className="size-3.5"
                                                />
                                            ) : sortDirection === 'asc' ? (
                                                <ArrowUp
                                                    aria-hidden="true"
                                                    className="size-3.5"
                                                />
                                            ) : (
                                                <ArrowDown
                                                    aria-hidden="true"
                                                    className="size-3.5"
                                                />
                                            )}
                                        </Button>
                                    ) : (
                                        column.header
                                    )}
                                </th>
                            ))}
                        </tr>
                    </thead>
                    <tbody>
                        {rows.length === 0 ? (
                            <tr>
                                <td
                                    colSpan={
                                        columns.length + (selectable ? 1 : 0)
                                    }
                                    className="text-muted-foreground px-4 py-12 text-center"
                                >
                                    {resolvedEmptyMessage}
                                </td>
                            </tr>
                        ) : (
                            rows.map((row) => (
                                <tr
                                    key={rowKey(row)}
                                    className="hover:bg-muted/25 border-t transition-colors"
                                >
                                    {selectable && (
                                        <td className="w-12 px-4 py-3">
                                            <Checkbox
                                                checked={selectedKeys.includes(
                                                    rowKey(row),
                                                )}
                                                onCheckedChange={(checked) =>
                                                    toggleRow(
                                                        row,
                                                        Boolean(checked),
                                                    )
                                                }
                                                aria-label={t('Select user')}
                                            />
                                        </td>
                                    )}
                                    {columns.map((column) => (
                                        <td
                                            key={column.key}
                                            className={cn(
                                                'px-4 py-2 align-middle',
                                                column.align === 'center' &&
                                                    'text-center',
                                                column.align === 'right' &&
                                                    'text-right',
                                                column.className,
                                            )}
                                        >
                                            {column.cell(row)}
                                        </td>
                                    ))}
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}
