import { AuditChangeList } from '@/components/admin/audit-change-list';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { useTranslation } from '@/hooks/use-translation';

type AuditRow = {
    description: string;
    event: string | null;
    causer: string | null;
    subject_type: string;
    subject_id: number | null;
    created_at: string | null;
    attribute_changes: Record<string, unknown>;
    properties: Record<string, unknown>;
};

export function AuditDetailDrawer({
    log,
    open,
    onOpenChange,
}: {
    log: AuditRow | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const { t, locale } = useTranslation();

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent className="w-full overflow-y-auto sm:max-w-2xl">
                <SheetHeader>
                    <SheetTitle>
                        {log?.description ?? t('Audit details')}
                    </SheetTitle>
                    <SheetDescription>
                        {log?.event ?? t('No event key')} ·{' '}
                        {log?.causer ?? t('System')}
                        {log?.created_at && (
                            <span className="block pt-1 text-xs">
                                {new Date(log.created_at).toLocaleString(
                                    locale === 'vi' ? 'vi-VN' : 'en-US',
                                )}
                            </span>
                        )}
                    </SheetDescription>
                </SheetHeader>
                {log && (
                    <div className="mt-6 space-y-4">
                        <div className="bg-muted/20 rounded-lg border p-3 text-sm">
                            <span className="font-medium">
                                {t('Subject')}:{' '}
                            </span>
                            {log.subject_type} #{log.subject_id ?? '—'}
                        </div>
                        <AuditChangeList
                            changes={log.attribute_changes}
                            properties={log.properties}
                        />
                    </div>
                )}
            </SheetContent>
        </Sheet>
    );
}
