import type { ReactNode } from 'react';

export function AdminTableCard({
    children,
    className = '',
}: {
    children: ReactNode;
    className?: string;
}) {
    return (
        <article
            className={`bg-background rounded-lg border p-3 shadow-xs ${className}`}
        >
            {children}
        </article>
    );
}
