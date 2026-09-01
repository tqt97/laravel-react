import { Head, Link, router } from '@inertiajs/react';
import {
    Edit,
    ListFilter,
    MoreVertical,
    ShieldCheck,
    Search,
    RotateCcw,
    Trash2,
    Undo2,
    UserRound,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import UserController from '@/actions/App/Http/Controllers/Admin/Users/UserController';
import { AddNewButton } from '@/components/admin/add-new-button';
import { BulkActionBar } from '@/components/admin/bulk-action-bar';
import { ConfirmDialog } from '@/components/admin/confirm-dialog';
import { AdminListPage } from '@/components/admin/admin-list-page';
import { AdminListToolbar } from '@/components/admin/admin-list-toolbar';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DataTable,
    type DataTableColumn,
    type SortDirection,
} from '@/components/admin/data-table';
import { PaginationNav } from '@/components/admin/pagination-nav';
import { PageHeader } from '@/components/admin/page-header';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { useTranslation } from '@/hooks/use-translation';
import { useInertiaLoading } from '@/hooks/use-inertia-loading';
import { PAGINATION_SIZES } from '@/lib/pagination';
import { dashboard } from '@/routes';
import type { AdminUser, AdminUserFilters } from '@/types/admin/users';
import type { PaginatedResource } from '@/types/admin/pagination';

export default function UsersIndex({
    users,
    filters,
}: {
    users: PaginatedResource<AdminUser>;
    filters: AdminUserFilters;
}) {
    const { t, supportedLocales } = useTranslation();
    const isLoading = useInertiaLoading();
    const [selectedKeys, setSelectedKeys] = useState<Array<number>>([]);
    const [confirmTarget, setConfirmTarget] = useState<{
        action:
            | 'delete'
            | 'restore'
            | 'force-delete'
            | 'bulk-delete'
            | 'bulk-restore'
            | 'bulk-force-delete';
        id?: number;
        name?: string;
    } | null>(null);
    const [processing, setProcessing] = useState(false);
    const [filterOpen, setFilterOpen] = useState(false);

    useEffect(() => {
        setSelectedKeys([]);
    }, [
        filters.search,
        filters.is_admin,
        filters.locale,
        filters.timezone,
        filters.trashed,
        filters.sort,
        filters.direction,
        filters.per_page,
        users.meta.current_page,
    ]);

    const preserveFilters = {
        search: filters.search || undefined,
        is_admin:
            filters.is_admin === null ? undefined : filters.is_admin ? 1 : 0,
        locale: filters.locale || undefined,
        timezone: filters.timezone || undefined,
        trashed: filters.trashed,
        sort: filters.sort,
        direction: filters.direction,
        per_page: filters.per_page,
        page: users.meta.current_page,
    };

    const applySort = (sort: string, direction: SortDirection) => {
        router.get(
            UserController.index.url(),
            { ...preserveFilters, sort, direction, page: 1 },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            },
        );
    };

    const applyPerPage = (perPage: number) => {
        router.get(
            UserController.index.url(),
            { ...preserveFilters, per_page: perPage, page: 1 },
            { preserveScroll: true, preserveState: true, replace: true },
        );
    };

    const activeFilterCount = [
        filters.is_admin !== null,
        Boolean(filters.locale),
        Boolean(filters.timezone),
        filters.trashed !== 'without',
    ].filter(Boolean).length;

    const columns: DataTableColumn<AdminUser>[] = [
        {
            key: 'name',
            header: t('Name'),
            cell: (user) => user.name,
            sortable: true,
        },
        {
            key: 'email',
            header: t('Email address'),
            cell: (user) => user.email,
            sortable: true,
        },
        {
            key: 'is_admin',
            header: t('Access'),
            cell: (user) => {
                const AccessIcon = user.is_admin ? ShieldCheck : UserRound;

                return (
                    <span className="inline-flex items-center justify-center gap-1.5">
                        <AccessIcon aria-hidden="true" className="size-4" />
                        {user.is_admin ? t('Administrator') : t('User')}
                    </span>
                );
            },
            sortable: true,
            align: 'left',
        },
        {
            key: 'locale',
            header: t('Language'),
            cell: (user) =>
                user.locale_label ? (
                    <span className="inline-flex items-center justify-center gap-1.5">
                        <span aria-hidden="true">{user.locale_flag}</span>
                        {user.locale_label}
                    </span>
                ) : (
                    '—'
                ),
            sortable: true,
            align: 'center',
        },
        {
            key: 'timezone',
            header: t('Timezone'),
            cell: (user) => user.timezone ?? '—',
            sortable: true,
            align: 'center',
        },
        {
            key: 'actions',
            header: t('Actions'),
            align: 'center',
            cell: (user) => (
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            type="button"
                            size="icon"
                            variant="ghost"
                            title={t('More actions')}
                            aria-label={t('More actions')}
                        >
                            <MoreVertical aria-hidden="true" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        {user.deleted_at ? (
                            <>
                                <DropdownMenuItem
                                    onSelect={() =>
                                        setConfirmTarget({
                                            action: 'restore',
                                            id: user.id,
                                            name: user.name,
                                        })
                                    }
                                >
                                    <Undo2 aria-hidden="true" />
                                    {t('Restore')}
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    variant="destructive"
                                    onSelect={() =>
                                        setConfirmTarget({
                                            action: 'force-delete',
                                            id: user.id,
                                            name: user.name,
                                        })
                                    }
                                >
                                    <Trash2 aria-hidden="true" />
                                    {t('Delete permanently')}
                                </DropdownMenuItem>
                            </>
                        ) : (
                            <>
                                <DropdownMenuItem asChild>
                                    <Link
                                        href={UserController.edit(user.id)}
                                        prefetch={false}
                                    >
                                        <Edit aria-hidden="true" />
                                        {t('Edit')}
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    variant="destructive"
                                    onSelect={() =>
                                        setConfirmTarget({
                                            action: 'delete',
                                            id: user.id,
                                            name: user.name,
                                        })
                                    }
                                >
                                    <Trash2 aria-hidden="true" />
                                    {t('Delete')}
                                </DropdownMenuItem>
                            </>
                        )}
                    </DropdownMenuContent>
                </DropdownMenu>
            ),
        },
    ];

    return (
        <AdminListPage labelledBy="admin-users-title">
            <Head title={t('Users')} />
            <PageHeader
                eyebrow={t('User management')}
                title={t('Users')}
                description={t(
                    'Manage users, roles and effective permissions in one screen. Backend policy remains the security boundary.',
                )}
                actions={<AddNewButton href={UserController.create()} />}
            />
            <h1 id="admin-users-title" className="sr-only">
                {t('Users')}
            </h1>
            <AdminListToolbar
                filters={
                    <div className="flex min-w-0 flex-col gap-3">
                        <form
                            id="user-filters"
                            method="get"
                            action={UserController.index.url()}
                            className="flex min-w-0 flex-wrap items-center gap-2"
                        >
                            <label className="min-w-0 flex-1 sm:min-w-64">
                                <span className="sr-only">
                                    {t('Search users')}
                                </span>
                                <Input
                                    name="search"
                                    defaultValue={filters.search}
                                    placeholder={t('Search users')}
                                    className="h-9"
                                />
                            </label>
                            <input
                                type="hidden"
                                name="sort"
                                value={filters.sort}
                            />
                            <input
                                type="hidden"
                                name="direction"
                                value={filters.direction}
                            />
                            <input
                                type="hidden"
                                name="per_page"
                                value={filters.per_page}
                            />
                            <input type="hidden" name="page" value={1} />
                            <input
                                type="hidden"
                                name="trashed"
                                value={filters.trashed}
                            />
                            <input
                                type="hidden"
                                name="timezone"
                                value={filters.timezone}
                            />
                            <input
                                type="hidden"
                                name="is_admin"
                                value={
                                    filters.is_admin === null
                                        ? ''
                                        : filters.is_admin
                                          ? '1'
                                          : '0'
                                }
                            />
                            <input
                                type="hidden"
                                name="locale"
                                value={filters.locale}
                            />
                            <Button
                                type="submit"
                                size="icon"
                                title={t('Apply filters')}
                                aria-label={t('Apply filters')}
                            >
                                <Search aria-hidden="true" />
                            </Button>
                            <Dialog
                                open={filterOpen}
                                onOpenChange={setFilterOpen}
                            >
                                <DialogTrigger asChild>
                                    <Button
                                        type="button"
                                        variant={
                                            activeFilterCount
                                                ? 'secondary'
                                                : 'outline'
                                        }
                                        size="icon"
                                        title={t('More filters')}
                                        aria-label={t('More filters')}
                                        className="relative"
                                    >
                                        <ListFilter aria-hidden="true" />
                                        {activeFilterCount > 0 && (
                                            <span className="bg-primary text-primary-foreground absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full text-[10px]">
                                                {activeFilterCount}
                                            </span>
                                        )}
                                    </Button>
                                </DialogTrigger>
                                <DialogContent className="max-h-[min(90vh,36rem)] overflow-y-auto">
                                    <DialogHeader>
                                        <DialogTitle>
                                            {t('More filters')}
                                        </DialogTitle>
                                        <DialogDescription>
                                            {t(
                                                'Refine the users shown in this list.',
                                            )}
                                        </DialogDescription>
                                    </DialogHeader>
                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <label className="grid gap-2 text-sm font-medium">
                                            {t('Access')}
                                            <select
                                                name="is_admin"
                                                form="user-filters"
                                                defaultValue={
                                                    filters.is_admin === null
                                                        ? ''
                                                        : filters.is_admin
                                                          ? '1'
                                                          : '0'
                                                }
                                                className="admin-filter-select"
                                            >
                                                <option value="">
                                                    {t('All access')}
                                                </option>
                                                <option value="1">
                                                    {t('Administrator')}
                                                </option>
                                                <option value="0">
                                                    {t('User')}
                                                </option>
                                            </select>
                                        </label>
                                        <label className="grid gap-2 text-sm font-medium">
                                            {t('Language')}
                                            <select
                                                name="locale"
                                                form="user-filters"
                                                defaultValue={filters.locale}
                                                className="admin-filter-select"
                                            >
                                                <option value="">
                                                    {t('All languages')}
                                                </option>
                                                {supportedLocales.map(
                                                    (locale) => (
                                                        <option
                                                            key={locale.value}
                                                            value={locale.value}
                                                        >
                                                            {locale.label}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                        </label>
                                        <label className="grid gap-2 text-sm font-medium">
                                            {t('User status')}
                                            <select
                                                name="trashed"
                                                form="user-filters"
                                                defaultValue={filters.trashed}
                                                className="admin-filter-select"
                                            >
                                                <option value="without">
                                                    {t('Active users')}
                                                </option>
                                                <option value="only">
                                                    {t('Deleted users')}
                                                </option>
                                                <option value="with">
                                                    {t('All users')}
                                                </option>
                                            </select>
                                        </label>
                                        <label className="grid gap-2 text-sm font-medium">
                                            {t('Timezone')}
                                            <Input
                                                name="timezone"
                                                form="user-filters"
                                                defaultValue={filters.timezone}
                                                placeholder={t('Timezone')}
                                                className="h-9"
                                            />
                                        </label>
                                    </div>
                                    <DialogFooter>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            onClick={() => setFilterOpen(false)}
                                        >
                                            {t('Cancel')}
                                        </Button>
                                        <Button
                                            type="submit"
                                            form="user-filters"
                                            onClick={() => setFilterOpen(false)}
                                        >
                                            {t('Apply filters')}
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                            <Button
                                asChild
                                type="button"
                                variant="ghost"
                                size="icon"
                                title={t('Clear filters')}
                                aria-label={t('Clear filters')}
                            >
                                <Link href={UserController.index.url()}>
                                    <RotateCcw aria-hidden="true" />
                                </Link>
                            </Button>
                        </form>
                        {activeFilterCount > 0 && (
                            <div className="flex flex-wrap items-center gap-2 text-xs">
                                <span className="text-muted-foreground">
                                    {t('Active filters')}:
                                </span>
                                {filters.is_admin !== null && (
                                    <Badge variant="secondary">
                                        {filters.is_admin
                                            ? t('Administrator')
                                            : t('User')}
                                    </Badge>
                                )}
                                {filters.locale && (
                                    <Badge variant="secondary">
                                        {filters.locale}
                                    </Badge>
                                )}
                                {filters.timezone && (
                                    <Badge variant="secondary">
                                        {filters.timezone}
                                    </Badge>
                                )}
                                {filters.trashed !== 'without' && (
                                    <Badge variant="secondary">
                                        {filters.trashed === 'only'
                                            ? t('Deleted users')
                                            : t('All users')}
                                    </Badge>
                                )}
                            </div>
                        )}
                    </div>
                }
            />
            <BulkActionBar
                count={filters.trashed === 'with' ? 0 : selectedKeys.length}
                onClear={() => setSelectedKeys([])}
                onDelete={() => setConfirmTarget({ action: 'bulk-delete' })}
                actions={
                    filters.trashed === 'only'
                        ? [
                              {
                                  key: 'restore',
                                  label: t('Restore selected'),
                                  icon: <Undo2 aria-hidden="true" />,
                                  onClick: () =>
                                      setConfirmTarget({
                                          action: 'bulk-restore',
                                      }),
                              },
                              {
                                  key: 'force-delete',
                                  label: t('Delete permanently selected'),
                                  icon: <Trash2 aria-hidden="true" />,
                                  destructive: true,
                                  onClick: () =>
                                      setConfirmTarget({
                                          action: 'bulk-force-delete',
                                      }),
                              },
                          ]
                        : undefined
                }
                busy={processing}
            />
            {filters.trashed === 'with' && (
                <p className="text-muted-foreground text-xs">
                    {t(
                        'Bulk actions are unavailable when active and deleted users are shown together.',
                    )}
                </p>
            )}
            <PaginationNav
                links={users.meta.links}
                from={users.meta.from}
                to={users.meta.to}
                total={users.meta.total}
                label={t('Users')}
                controls={
                    <div className="text-muted-foreground flex min-w-0 items-center gap-2 text-sm">
                        <select
                            value={filters.per_page}
                            aria-label={t('Rows per page')}
                            onChange={(event) =>
                                applyPerPage(Number(event.target.value))
                            }
                            className="admin-filter-select w-auto min-w-28"
                        >
                            {PAGINATION_SIZES.map((size) => (
                                <option key={size} value={size}>
                                    {size} {t('per page')}
                                </option>
                            ))}
                        </select>
                    </div>
                }
            />
            <div
                className="relative transition-opacity duration-200 motion-reduce:transition-none"
                aria-busy={isLoading}
            >
                <div className={isLoading ? 'opacity-60' : undefined}>
                    <DataTable
                        rows={users.data}
                        columns={columns}
                        rowKey={(user) => user.id}
                        ariaLabel={t('Users')}
                        emptyMessage={t('No users in your visible scope.')}
                        sortKey={filters.sort}
                        sortDirection={filters.direction}
                        onSortChange={applySort}
                        selectable={filters.trashed !== 'with'}
                        selectedKeys={selectedKeys}
                        onSelectionChange={(keys) =>
                            setSelectedKeys(keys as number[])
                        }
                    />
                </div>
                {isLoading && (
                    <div
                        className="bg-background/45 absolute inset-0 z-10 flex items-start justify-center pt-16 backdrop-blur-[1px]"
                        role="status"
                        aria-label={t('Loading…', 'Loading…')}
                    >
                        <div className="bg-card text-muted-foreground flex items-center gap-2 rounded-full border px-3 py-2 text-xs shadow-sm">
                            <Spinner />
                            {t('Loading…', 'Loading…')}
                        </div>
                    </div>
                )}
            </div>
            <ConfirmDialog
                open={confirmTarget !== null}
                onOpenChange={(open) => {
                    if (!open) setConfirmTarget(null);
                }}
                title={
                    confirmTarget?.action === 'restore'
                        ? t('Restore user?')
                        : confirmTarget?.action === 'bulk-restore'
                          ? t('Restore selected users?')
                          : confirmTarget?.action === 'force-delete'
                            ? t('Delete user permanently?')
                            : confirmTarget?.action === 'bulk-force-delete'
                              ? t('Delete selected users permanently?')
                              : t('Delete user?')
                }
                description={
                    confirmTarget?.action === 'restore'
                        ? t('The user will become active again.')
                        : confirmTarget?.action === 'bulk-restore'
                          ? t('Selected users will be restored.')
                          : confirmTarget?.action === 'force-delete'
                            ? t(
                                  'This user will be permanently removed and cannot be restored.',
                              )
                            : confirmTarget?.action === 'bulk-force-delete'
                              ? t(
                                    'Selected users will be permanently removed and cannot be restored.',
                                )
                              : confirmTarget?.action === 'bulk-delete'
                                ? t(
                                      'Selected users will be moved to the trash.',
                                  )
                                : t(
                                      'The user will be moved to the trash and can be restored later.',
                                  )
                }
                destructive={
                    confirmTarget?.action !== 'restore' &&
                    confirmTarget?.action !== 'bulk-restore'
                }
                busy={processing}
                detail={confirmTarget?.name}
                onConfirm={() => {
                    setProcessing(true);
                    if (confirmTarget?.action === 'bulk-delete') {
                        router.post(
                            UserController.bulkDestroy.url({
                                query: preserveFilters,
                            }),
                            { ids: selectedKeys },
                            {
                                onSuccess: () => setSelectedKeys([]),
                                onFinish: () => {
                                    setConfirmTarget(null);
                                    setProcessing(false);
                                },
                            },
                        );
                    } else if (confirmTarget?.action === 'bulk-restore') {
                        router.post(
                            UserController.bulkRestore.url({
                                query: preserveFilters,
                            }),
                            { ids: selectedKeys },
                            {
                                onSuccess: () => setSelectedKeys([]),
                                onFinish: () => {
                                    setConfirmTarget(null);
                                    setProcessing(false);
                                },
                            },
                        );
                    } else if (confirmTarget?.action === 'bulk-force-delete') {
                        router.post(
                            UserController.bulkForceDestroy.url({
                                query: preserveFilters,
                            }),
                            { ids: selectedKeys },
                            {
                                onSuccess: () => setSelectedKeys([]),
                                onFinish: () => {
                                    setConfirmTarget(null);
                                    setProcessing(false);
                                },
                            },
                        );
                    } else if (
                        confirmTarget?.action === 'delete' &&
                        confirmTarget.id
                    ) {
                        router.delete(
                            UserController.destroy(confirmTarget.id, {
                                query: preserveFilters,
                            }).url,
                            {
                                onFinish: () => {
                                    setConfirmTarget(null);
                                    setProcessing(false);
                                },
                            },
                        );
                    } else if (
                        confirmTarget?.action === 'restore' &&
                        confirmTarget.id
                    ) {
                        router.patch(
                            UserController.restore(confirmTarget.id, {
                                query: preserveFilters,
                            }).url,
                            {},
                            {
                                onFinish: () => {
                                    setConfirmTarget(null);
                                    setProcessing(false);
                                },
                            },
                        );
                    } else if (
                        confirmTarget?.action === 'force-delete' &&
                        confirmTarget.id
                    ) {
                        router.delete(
                            UserController.forceDestroy(confirmTarget.id, {
                                query: preserveFilters,
                            }).url,
                            {
                                onFinish: () => {
                                    setConfirmTarget(null);
                                    setProcessing(false);
                                },
                            },
                        );
                    }
                }}
            />
        </AdminListPage>
    );
}

UsersIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: 'Users', href: UserController.index() },
    ],
};
