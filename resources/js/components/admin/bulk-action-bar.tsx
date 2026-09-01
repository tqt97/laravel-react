import { Trash2, X } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';

export type BulkAction = {
    key: string;
    label: ReactNode;
    onClick: () => void;
    destructive?: boolean;
    disabled?: boolean;
    icon?: ReactNode;
};

export function BulkActionBar({
    count,
    onClear,
    onDelete,
    busy = false,
    actions,
}: {
    count: number;
    onClear: () => void;
    onDelete: () => void;
    busy?: boolean;
    actions?: BulkAction[];
}) {
    const { t } = useTranslation();

    if (count < 1) {
        return null;
    }

    const resolvedActions: BulkAction[] = actions ?? [
        {
            key: 'delete',
            label: t('Delete selected'),
            onClick: onDelete,
            destructive: true,
            icon: <Trash2 />,
        },
    ];

    return (
        <div className="border-primary/30 bg-primary/5 flex flex-wrap items-center justify-between gap-3 rounded-xl border px-4 py-3 text-sm">
            <span className="font-medium">
                {t('Selected')} {count} {t('items')}
            </span>
            <div className="flex items-center gap-2">
                <Button
                    type="button"
                    size="sm"
                    variant="ghost"
                    onClick={onClear}
                    disabled={busy}
                >
                    <X />
                    {t('Clear selection')}
                </Button>
                {resolvedActions.map((action) => (
                    <Button
                        key={action.key}
                        type="button"
                        size="sm"
                        variant={action.destructive ? 'destructive' : 'outline'}
                        onClick={action.onClick}
                        disabled={busy || action.disabled}
                    >
                        {action.icon}
                        {action.label}
                    </Button>
                ))}
            </div>
        </div>
    );
}
