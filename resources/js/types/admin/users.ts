import type { Locale } from '@/types/locale';

export type AdminUser = {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    locale: Locale | null;
    locale_label: string | null;
    locale_flag: string | null;
    timezone: string | null;
    email_verified_at: string | null;
    created_at: string | null;
    deleted_at: string | null;
};

export type AdminUserFormUser = Pick<
    AdminUser,
    'name' | 'email' | 'locale' | 'timezone' | 'is_admin'
>;

export type AdminUserFilters = {
    search: string;
    is_admin: boolean | null;
    locale: Locale | '';
    timezone: string;
    trashed: 'without' | 'with' | 'only';
    sort: string;
    direction: 'asc' | 'desc';
    per_page: number;
};
