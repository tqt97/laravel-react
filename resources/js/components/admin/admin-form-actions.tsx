import type { ReactNode } from 'react';
import { RotateCcw, Save } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';

export function AdminFormActions({
    processing = false,
    onCancel,
    submitLabel,
    processingLabel,
    children,
}: {
    processing?: boolean;
    onCancel?: () => void;
    submitLabel?: ReactNode;
    processingLabel?: ReactNode;
    children?: ReactNode;
}) {
    const { t } = useTranslation();

    return (
        <div
            className="bg-card/95 supports-[backdrop-filter]:bg-card/80 sticky bottom-4 z-10 flex flex-wrap justify-end gap-2 rounded-xl border p-3 shadow-lg backdrop-blur"
            aria-busy={processing}
        >
            {onCancel && (
                <Button
                    type="button"
                    variant="outline"
                    disabled={processing}
                    onClick={onCancel}
                >
                    {t('Cancel', 'Cancel')}
                </Button>
            )}
            {children ?? (
                <>
                    <Button
                        type="reset"
                        variant="outline"
                        disabled={processing}
                        title={t('Reset')}
                    >
                        <RotateCcw aria-hidden="true" />
                        {t('Reset')}
                    </Button>
                    <Button type="submit" disabled={processing}>
                        <Save aria-hidden="true" />
                        {processing
                            ? (processingLabel ??
                              t('Processing…', 'Processing…'))
                            : (submitLabel ?? t('Save', 'Save'))}
                    </Button>
                </>
            )}
        </div>
    );
}
