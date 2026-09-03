import { ChevronDown, ChevronsUpDown } from 'lucide-react';
import { useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { useTranslation } from '@/hooks/use-translation';
import type { PermissionDefinition } from '@/components/admin/types';

export function PermissionChecklist({
    permissions,
    selected: selectedValues,
    onSelectedChange,
    name = 'permissions[]',
    disabled = false,
}: {
    permissions: PermissionDefinition[];
    selected: string[];
    onSelectedChange?: (selected: string[]) => void;
    name?: string;
    disabled?: boolean;
}) {
    const { t } = useTranslation();
    const [internalSelected, setInternalSelected] = useState(
        () => new Set(selectedValues),
    );
    const selected = useMemo(
        () => (onSelectedChange ? new Set(selectedValues) : internalSelected),
        [internalSelected, onSelectedChange, selectedValues],
    );
    const [query, setQuery] = useState('');
    const [openGroups, setOpenGroups] = useState(
        () =>
            new Set(
                permissions
                    .filter((permission) =>
                        selectedValues.includes(permission.name),
                    )
                    .map((permission) => permission.group),
            ),
    );

    const groups = useMemo(() => {
        const filtered = permissions.filter((permission) => {
            const term = query.trim().toLowerCase();

            return (
                !term ||
                permission.name.toLowerCase().includes(term) ||
                permission.label.toLowerCase().includes(term)
            );
        });

        return filtered.reduce<Record<string, PermissionDefinition[]>>(
            (result, permission) => {
                (result[permission.group] ??= []).push(permission);

                return result;
            },
            {},
        );
    }, [permissions, query]);

    const allSelected =
        permissions.length > 0 &&
        permissions.every((permission) => selected.has(permission.name));
    const groupNames = Object.keys(groups);
    const allGroupsOpen =
        groupNames.length > 0 &&
        groupNames.every((group) => openGroups.has(group));

    const setGroupOpen = (group: string, open: boolean) => {
        setOpenGroups((current) => {
            const next = new Set(current);

            if (open) {
                next.add(group);
            } else {
                next.delete(group);
            }

            return next;
        });
    };

    const toggleAllGroups = () => {
        setOpenGroups(allGroupsOpen ? new Set() : new Set(groupNames));
    };

    const setPermission = (permission: string, checked: boolean) => {
        const update = (current: Set<string>) => {
            const next = new Set(current);

            if (checked) {
                next.add(permission);
            } else {
                next.delete(permission);
            }

            return next;
        };
        const next = update(selected);
        onSelectedChange?.([...next]);

        if (!onSelectedChange) {
            setInternalSelected(next);
        }
    };
    const setMany = (items: PermissionDefinition[], checked: boolean) => {
        const update = (current: Set<string>) => {
            const next = new Set(current);
            items.forEach((item) => {
                if (checked) {
                    next.add(item.name);
                } else {
                    next.delete(item.name);
                }
            });

            return next;
        };
        const next = update(selected);
        onSelectedChange?.([...next]);

        if (!onSelectedChange) {
            setInternalSelected(next);
        }
    };

    return (
        <div className="space-y-3">
            <div className="bg-muted/25 grid gap-3 rounded-xl border p-3 md:grid-cols-[minmax(0,1fr)_auto_auto] md:items-center">
                <Input
                    value={query}
                    onChange={(event) => setQuery(event.target.value)}
                    placeholder={t(
                        'permissions.search_placeholder',
                        'Search permissions by name or key…',
                    )}
                    aria-label={t(
                        'permissions.search_aria',
                        'Search permissions',
                    )}
                />
                <label className="flex cursor-pointer items-center gap-2 text-sm font-medium">
                    <Checkbox
                        checked={allSelected}
                        disabled={disabled}
                        onCheckedChange={(checked) =>
                            setMany(permissions, checked === true)
                        }
                    />
                    {t('permissions.select_all_count', {
                        selected: selected.size,
                        total: permissions.length,
                    })}
                </label>
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    onClick={toggleAllGroups}
                >
                    <ChevronsUpDown />
                    {allGroupsOpen
                        ? t('permissions.collapse_all', 'Collapse all')
                        : t('permissions.expand_all', 'Expand all')}
                </Button>
            </div>

            <div className="grid items-start gap-3 xl:grid-cols-2 2xl:grid-cols-3">
                {Object.entries(groups).map(([group, items]) => {
                    const groupSelected =
                        items.length > 0 &&
                        items.every((item) => selected.has(item.name));
                    const isOpen = query.trim() !== '' || openGroups.has(group);

                    return (
                        <Collapsible
                            key={group}
                            open={isOpen}
                            onOpenChange={(open) => setGroupOpen(group, open)}
                            className="bg-card overflow-hidden rounded-xl border"
                        >
                            <div className="bg-muted/30 flex min-h-14 items-center justify-between gap-2 px-3 py-2">
                                <CollapsibleTrigger asChild>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        className="h-auto min-w-0 flex-1 justify-start px-1 py-1 text-left"
                                    >
                                        <ChevronDown
                                            className={`transition-transform ${isOpen ? 'rotate-180' : ''}`}
                                        />
                                        <span className="min-w-0">
                                            <strong className="block truncate text-sm">
                                                {group}
                                            </strong>
                                            <span className="text-muted-foreground block text-xs">
                                                {t('permissions.item_count', {
                                                    count: items.length,
                                                })}
                                            </span>
                                        </span>
                                    </Button>
                                </CollapsibleTrigger>
                                <label className="flex shrink-0 cursor-pointer items-center gap-1.5 text-xs font-medium">
                                    <Checkbox
                                        checked={groupSelected}
                                        disabled={disabled}
                                        onCheckedChange={(checked) =>
                                            setMany(items, checked === true)
                                        }
                                    />
                                    {t(
                                        'permissions.select_group',
                                        'Select group',
                                    )}
                                </label>
                            </div>
                            <CollapsibleContent>
                                <div className="grid gap-1.5 border-t p-2">
                                    {items.map((permission) => (
                                        <label
                                            key={permission.name}
                                            className="hover:border-border hover:bg-muted/35 flex cursor-pointer items-start gap-2.5 rounded-md border border-transparent px-2.5 py-2 transition-colors"
                                        >
                                            <Checkbox
                                                value={permission.name}
                                                checked={selected.has(
                                                    permission.name,
                                                )}
                                                disabled={disabled}
                                                onCheckedChange={(checked) =>
                                                    setPermission(
                                                        permission.name,
                                                        checked === true,
                                                    )
                                                }
                                                className="mt-0.5 cursor-pointer"
                                            />
                                            <span className="min-w-0">
                                                <strong className="block truncate text-sm leading-5">
                                                    {permission.label}
                                                </strong>
                                                <code className="text-muted-foreground block truncate text-[11px]">
                                                    {permission.name}
                                                </code>
                                            </span>
                                        </label>
                                    ))}
                                </div>
                            </CollapsibleContent>
                        </Collapsible>
                    );
                })}
            </div>
            {Array.from(selected).map((permission) => (
                <input
                    key={permission}
                    type="hidden"
                    name={name}
                    value={permission}
                />
            ))}
        </div>
    );
}
