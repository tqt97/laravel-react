import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import type { AdminPaginationLink } from '@/types/admin/pagination';

function paginationLabel(label: string): string {
    return label
        .replace(/&laquo;/g, '«')
        .replace(/&raquo;/g, '»')
        .replace(/<[^>]*>/g, '');
}

export function PaginationNav({
    links,
    from,
    to,
    total,
    label,
    controls,
}: {
    links: AdminPaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
    label: string;
    controls?: ReactNode;
}) {
    const { t } = useTranslation();

    if (total === 0) {
        return null;
    }

    return (
        <div className="bg-card text-muted-foreground flex flex-col gap-4 rounded-xl border px-4 py-3 text-sm lg:flex-row lg:items-center lg:justify-between">
            <div className="flex flex-wrap items-center gap-3">
                <span className="font-medium">
                    {from ?? 0}–{to ?? 0} / {total} {label}
                </span>
            </div>
            <div className="flex min-w-0 flex-wrap items-center gap-3 lg:justify-end">
                {controls}
                {links.length > 3 && (
                    <nav
                        aria-label={t('Pagination', 'Pagination')}
                        className="flex flex-wrap gap-1"
                    >
                        {links.map((link, index) => (
                            <Button
                                key={`${link.label}-${index}`}
                                asChild={Boolean(link.url)}
                                disabled={!link.url}
                                size="sm"
                                variant={link.active ? 'default' : 'outline'}
                            >
                                {link.url ? (
                                    <Link
                                        href={link.url}
                                        preserveScroll
                                        preserveState
                                    >
                                        {paginationLabel(link.label)}
                                    </Link>
                                ) : (
                                    <span>{paginationLabel(link.label)}</span>
                                )}
                            </Button>
                        ))}
                    </nav>
                )}
            </div>
        </div>
    );
}
