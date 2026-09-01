import type { InertiaLinkProps } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import type { TranslationKey } from '@/types/locale';

export type BreadcrumbItem = {
    title: TranslationKey;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    title: TranslationKey;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
};
