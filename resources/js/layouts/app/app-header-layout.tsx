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
                {children}
            </AppContent>
        </AppShell>
    );
}
