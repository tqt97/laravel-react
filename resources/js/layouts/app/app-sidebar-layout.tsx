import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { AppSidebar } from '@/components/app-sidebar';
import { AppSidebarHeader } from '@/components/app-sidebar-header';
import { SkipLink } from '@/components/skip-link';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <SkipLink />
            <AppSidebar />
            <AppContent
                id="main-content"
                tabIndex={-1}
                variant="sidebar"
                className="min-w-0 overflow-x-clip outline-none"
            >
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                <div className="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-5 px-3 py-5 sm:px-4 md:gap-6 md:py-6 lg:px-0">
                    {children}
                </div>
            </AppContent>
        </AppShell>
    );
}
