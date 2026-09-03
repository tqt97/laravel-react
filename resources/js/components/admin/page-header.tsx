import type { ReactNode } from 'react';

export function PageHeader({
    eyebrow,
    title,
    description,
    actions,
}: {
    eyebrow?: string;
    title: string;
    description?: string;
    actions?: ReactNode;
}) {
    return (
        <div className="flex flex-col gap-4 border-b pb-5 lg:flex-row lg:items-end lg:justify-between">
            <div className="min-w-0 space-y-1.5">
                {eyebrow && (
                    <p className="text-primary text-xs font-semibold tracking-[0.16em] uppercase">
                        {eyebrow}
                    </p>
                )}
                <h1 className="text-2xl font-semibold tracking-tight md:text-3xl">
                    {title}
                </h1>
                {description && (
                    <p className="text-muted-foreground max-w-3xl text-sm leading-6">
                        {description}
                    </p>
                )}
            </div>
            {actions && (
                <div className="flex shrink-0 flex-wrap gap-2">{actions}</div>
            )}
        </div>
    );
}
