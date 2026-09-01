import { Badge } from '@/components/ui/badge';
import { useTranslation } from '@/hooks/use-translation';

type AuditChanges = Record<string, unknown>;

const labels: Record<string, string> = {
    name: 'Name',
    email: 'Email',
    guard_name: 'Guard',
    roles: 'Roles',
    permissions: 'Permissions',
    actor_id: 'Actor ID',
    target_id: 'Target ID',
    subject_id: 'Subject ID',
    subject_type: 'Subject type',
    log_name: 'Log name',
    authorization: 'Authorization',
    event: 'Event',
    created_at: 'Created at',
    orphan: 'Orphan permissions',
    created: 'Created / new permissions',
    pruned: 'Pruned permissions',
    updated: 'Updated',
    deleted: 'Deleted',
    audit_source: 'Audit source',
    audit_version: 'Audit version',
    request_id: 'Request ID',
};

function display(value: unknown): string {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (Array.isArray(value)) {
        return (
            value
                .map((item) =>
                    typeof item === 'object' && item !== null
                        ? JSON.stringify(item)
                        : String(item),
                )
                .join(', ') || '—'
        );
    }

    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    if (
        typeof value === 'string' ||
        typeof value === 'number' ||
        typeof value === 'boolean' ||
        typeof value === 'bigint'
    ) {
        return String(value);
    }

    return JSON.stringify(value) ?? '—';
}

function safeKey(key: string): boolean {
    return !/(password|secret|token|recovery)/i.test(key);
}

function keyLabel(
    key: string,
    t: (key: string, fallback?: string) => string,
): string {
    const fallback =
        labels[key] ??
        key
            .replace(/_/g, ' ')
            .replace(/\b\w/g, (character) => character.toUpperCase());

    return t(`audit.fields.${key}`, fallback);
}

function displayValue(
    value: unknown,
    t: (key: string, fallback?: string) => string,
): string {
    if (typeof value !== 'string') {
        return display(value);
    }

    if (value.startsWith('authorization.')) {
        return value.replace(/^authorization\./, '').replace(/_/g, ' ');
    }

    const [resource, action] = value.split('.');
    const resources: Record<string, string> = {
        users: t('audit.resources.users', 'Users'),
        roles: t('audit.resources.roles', 'Roles'),
        permissions: t('audit.resources.permissions', 'Permissions'),
        audit: t('audit.resources.audit', 'Audit'),
        dashboard: 'Dashboard',
    };
    const actions: Record<string, string> = {
        view: t('audit.actions.view', 'View'),
        manage: t('audit.actions.manage', 'Manage'),
        create: t('audit.actions.create', 'Create'),
        update: t('audit.actions.update', 'Update'),
        delete: t('audit.actions.delete', 'Delete'),
        sync: t('audit.actions.sync', 'Sync'),
    };

    if (resource && action && resources[resource] && actions[action]) {
        return `${resources[resource]} · ${actions[action]} (${value})`;
    }

    return value;
}

function changeType(
    oldValue: unknown,
    newValue: unknown,
): 'added' | 'removed' | 'changed' {
    if (oldValue === undefined) {
        return 'added';
    }

    if (newValue === undefined) {
        return 'removed';
    }

    return 'changed';
}

export function AuditChangeList({
    changes,
    properties = {},
}: {
    changes?: AuditChanges;
    properties?: AuditChanges;
}) {
    const { t, td } = useTranslation();
    const attributes = (changes?.attributes ?? {}) as AuditChanges;
    const old = (changes?.old ?? {}) as AuditChanges;
    const keys = Array.from(
        new Set([...Object.keys(old), ...Object.keys(attributes)]),
    ).filter(safeKey);
    const propertyEntries = Object.entries(properties).filter(([key]) =>
        safeKey(key),
    );

    if (keys.length === 0 && propertyEntries.length === 0) {
        return (
            <span className="text-muted-foreground text-sm">
                {t('No changes.')}
            </span>
        );
    }

    return (
        <div className="space-y-3 text-sm">
            {keys.map((key) => (
                <div
                    key={key}
                    className="bg-muted/20 grid gap-2 rounded-lg border p-3 sm:grid-cols-[140px_minmax(0,1fr)_minmax(0,1fr)]"
                >
                    <div className="space-y-1">
                        <strong>{keyLabel(key, td)}</strong>
                        <Badge variant="outline">
                            {td(changeType(old[key], attributes[key]))}
                        </Badge>
                    </div>
                    <div>
                        <span className="text-muted-foreground block text-xs">
                            {t('Before')}
                        </span>
                        <span className="break-words">
                            {displayValue(old[key], td)}
                        </span>
                    </div>
                    <div>
                        <span className="text-muted-foreground block text-xs">
                            {t('After')}
                        </span>
                        <span className="break-words">
                            {displayValue(attributes[key], td)}
                        </span>
                    </div>
                </div>
            ))}
            {propertyEntries.map(([key, value]) => (
                <div
                    key={key}
                    className="bg-muted/20 flex flex-wrap items-center gap-2 rounded-lg border p-3"
                >
                    <strong>{keyLabel(key, td)}</strong>
                    {Array.isArray(value) ? (
                        value.map((item) => (
                            <Badge key={String(item)} variant="secondary">
                                {displayValue(item, td)}
                            </Badge>
                        ))
                    ) : (
                        <span className="break-words">
                            {displayValue(value, td)}
                        </span>
                    )}
                </div>
            ))}
        </div>
    );
}
