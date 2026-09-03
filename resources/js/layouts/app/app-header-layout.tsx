import { AppContent } from '@/components/app-content';
import { AppHeader } from '@/components/app-header';
import { AppShell } from '@/components/app-shell';
import { SkipLink } from '@/components/skip-link';
import type { AppLayoutProps } from '@/types';

export default function AppHeaderLayout({
    children,
    breadcrumbs,
}: AppLayoutProps) {
    return (
        <AppShell variant="header">
            <SkipLink />
            <AppHeader breadcrumbs={breadcrumbs} />
            <AppContent id="main-content" tabIndex={-1} variant="header">
                <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-5 px-3 py-5 sm:px-4 md:gap-6 md:py-6 lg:px-5">
                    {children}
                </div>
            </AppContent>
        </AppShell>
    );
}
