import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export function AdminListPage({
    children,
    className = '',
    labelledBy,
}: {
    children: ReactNode;
    className?: string;
    labelledBy?: string;
}) {
    return (
        <section
            className={cn('w-full space-y-6', className)}
            aria-labelledby={labelledBy}
        >
            {children}
        </section>
    );
}
