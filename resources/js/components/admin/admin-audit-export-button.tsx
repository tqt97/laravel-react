import { Download, LoaderCircle } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import { toast } from 'sonner';

export function AdminAuditExportButton({
    format,
    filters,
}: {
    format: 'csv' | 'json';
    filters: Record<string, string | number>;
}) {
    const { t } = useTranslation();
    const [busy, setBusy] = useState(false);
    const timer = useRef<ReturnType<typeof setTimeout> | null>(null);
    const controller = useRef<AbortController | null>(null);

    useEffect(
        () => () => {
            if (timer.current) {
                clearTimeout(timer.current);
            }
            controller.current?.abort();
        },
        [],
    );

    const exportFile = async () => {
        setBusy(true);
        controller.current?.abort();
        controller.current = new AbortController();

        try {
            const params = new URLSearchParams({
                ...Object.fromEntries(
                    Object.entries(filters).map(([key, value]) => [
                        key,
                        String(value),
                    ]),
                ),
                queue: '1',
            });
            const response = await fetch(
                `/audit/logs/export/${format}?${params}`,
                {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    signal: controller.current.signal,
                },
            );

            if (!response.ok) {
                throw new Error('Export request failed');
            }

            const queued = (await response.json()) as { status_url: string };

            let attempts = 0;
            const poll = async (): Promise<void> => {
                attempts += 1;

                if (attempts > 60) {
                    throw new Error('Export status polling timed out');
                }

                const statusResponse = await fetch(queued.status_url, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    signal: controller.current?.signal,
                });

                if (!statusResponse.ok) {
                    throw new Error('Export status request failed');
                }
                const status = (await statusResponse.json()) as {
                    status: string;
                    download_url?: string | null;
                };

                if (status.status === 'completed' && status.download_url) {
                    window.location.assign(status.download_url);
                    setBusy(false);

                    return;
                }

                if (status.status === 'failed') {
                    throw new Error('Export failed');
                }

                timer.current = setTimeout(poll, 1000);
            };
            await poll();
        } catch {
            if (controller.current?.signal.aborted) {
                return;
            }

            toast.error(t('Something went wrong.'));
            setBusy(false);
        }
    };

    return (
        <Button
            type="button"
            variant="outline"
            size="sm"
            onClick={exportFile}
            disabled={busy}
        >
            {busy ? <LoaderCircle className="animate-spin" /> : <Download />}
            {busy
                ? t('Preparing export…', 'Preparing export…')
                : format.toUpperCase()}
        </Button>
    );
}
