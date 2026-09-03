import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

/**
 * Exposes Inertia navigation state for page-level loading placeholders.
 */
export function useInertiaLoading(): boolean {
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        let loadingTimer: ReturnType<typeof setTimeout> | undefined;

        const removeStartListener = router.on('start', () => {
            loadingTimer = setTimeout(() => setLoading(true), 120);
        });
        const removeFinishListener = router.on('finish', () => {
            if (loadingTimer !== undefined) {
                clearTimeout(loadingTimer);
            }

            loadingTimer = undefined;
            setLoading(false);
        });

        return () => {
            if (loadingTimer !== undefined) {
                clearTimeout(loadingTimer);
            }

            removeStartListener();
            removeFinishListener();
        };
    }, []);

    return loading;
}
