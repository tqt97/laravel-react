import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useTranslation } from '@/hooks/use-translation';

export function ConfirmDialog({
    open,
    onOpenChange,
    title,
    description,
    confirmLabel,
    cancelLabel,
    busy = false,
    destructive = false,
    detail,
    onConfirm,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description: string;
    confirmLabel?: ReactNode;
    cancelLabel?: ReactNode;
    busy?: boolean;
    destructive?: boolean;
    detail?: ReactNode;
    onConfirm: () => void;
}) {
    const { t } = useTranslation();
    const resolvedConfirmLabel = confirmLabel ?? t('Confirm', 'Confirm');
    const resolvedCancelLabel = cancelLabel ?? t('Cancel', 'Cancel');

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                    <DialogDescription>{description}</DialogDescription>
                </DialogHeader>
                {detail && (
                    <div className="bg-muted/40 text-muted-foreground rounded-lg border p-3 text-sm">
                        {detail}
                    </div>
                )}
                <DialogFooter>
                    <Button
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                        disabled={busy}
                    >
                        {resolvedCancelLabel}
                    </Button>
                    <Button
                        variant={destructive ? 'destructive' : 'default'}
                        onClick={onConfirm}
                        disabled={busy}
                    >
                        {busy
                            ? t('Processing…', 'Processing…')
                            : resolvedConfirmLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
