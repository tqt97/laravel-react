import { Form } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { useState } from 'react';
import { AdminFormActions } from '@/components/admin/admin-form-actions';
import InputError from '@/components/input-error';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/hooks/use-translation';
import type { AdminUserFormUser } from '@/types/admin/users';

function FieldLabel({
    htmlFor,
    children,
    required = false,
}: {
    htmlFor: string;
    children: ReactNode;
    required?: boolean;
}) {
    return (
        <Label htmlFor={htmlFor}>
            {children}
            {required && (
                <span className="text-destructive ml-1" aria-hidden="true">
                    *
                </span>
            )}
        </Label>
    );
}

export function UserForm({
    action,
    method,
    user,
}: {
    action: string;
    method: 'post' | 'put';
    user?: AdminUserFormUser;
}) {
    const { t, supportedLocales: locales } = useTranslation();
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');

    return (
        <Form
            action={action}
            method={method}
            className="space-y-6"
            onReset={() => {
                setPassword('');
                setPasswordConfirmation('');
            }}
        >
            {({ errors, processing }) => (
                <>
                    <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
                        <section className="bg-card min-w-0 rounded-xl border p-4 shadow-sm sm:p-6">
                            <div className="mb-6 border-b pb-4">
                                <h2 className="text-base font-semibold">
                                    {t('Account information')}
                                </h2>
                                <p className="text-muted-foreground mt-1 text-sm">
                                    {t(
                                        'Manage the user profile and sign-in details.',
                                    )}
                                </p>
                            </div>
                            <div className="grid gap-5 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <FieldLabel htmlFor="name" required>
                                        {t('Name')}
                                    </FieldLabel>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={user?.name}
                                        required
                                        autoComplete="name"
                                        aria-invalid={Boolean(errors.name)}
                                        aria-describedby={
                                            errors.name
                                                ? 'name-error'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        id="name-error"
                                        message={errors.name}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <FieldLabel htmlFor="email" required>
                                        {t('Email address')}
                                    </FieldLabel>
                                    <Input
                                        id="email"
                                        name="email"
                                        type="email"
                                        defaultValue={user?.email}
                                        required
                                        autoComplete="email"
                                        aria-invalid={Boolean(errors.email)}
                                        aria-describedby={
                                            errors.email
                                                ? 'email-error'
                                                : undefined
                                        }
                                    />
                                    <InputError
                                        id="email-error"
                                        message={errors.email}
                                    />
                                </div>

                                <div className="grid gap-5 md:col-span-2 md:grid-cols-2">
                                    <div className="grid gap-2">
                                        <div className="flex min-h-5 items-center justify-between gap-2">
                                            <FieldLabel
                                                htmlFor="password"
                                                required={!user}
                                            >
                                                {user
                                                    ? t('New password')
                                                    : t('common.password')}
                                            </FieldLabel>
                                            {user && (
                                                <span className="text-muted-foreground text-right text-xs">
                                                    {t(
                                                        'Leave blank to keep the current password.',
                                                    )}
                                                </span>
                                            )}
                                        </div>
                                        <Input
                                            id="password"
                                            name="password"
                                            type="password"
                                            required={
                                                !user ||
                                                Boolean(passwordConfirmation)
                                            }
                                            onChange={(event) =>
                                                setPassword(event.target.value)
                                            }
                                            autoComplete={
                                                user
                                                    ? 'new-password'
                                                    : 'new-password'
                                            }
                                            aria-invalid={Boolean(
                                                errors.password,
                                            )}
                                            aria-describedby={
                                                errors.password
                                                    ? 'password-error'
                                                    : undefined
                                            }
                                        />
                                        <div className="min-h-5">
                                            <InputError
                                                id="password-error"
                                                message={errors.password}
                                            />
                                        </div>
                                    </div>

                                    <div className="grid gap-2">
                                        <div className="flex min-h-5 items-center justify-between gap-2">
                                            <FieldLabel
                                                htmlFor="password_confirmation"
                                                required={!user}
                                            >
                                                {t('auth.confirm_password')}
                                            </FieldLabel>
                                            <span
                                                aria-hidden="true"
                                                className="invisible text-xs"
                                            >
                                                {t(
                                                    'Leave blank to keep the current password.',
                                                )}
                                            </span>
                                        </div>
                                        <Input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            required={
                                                !user || Boolean(password)
                                            }
                                            autoComplete="new-password"
                                            onChange={(event) =>
                                                setPasswordConfirmation(
                                                    event.target.value,
                                                )
                                            }
                                            aria-invalid={Boolean(
                                                errors.password_confirmation,
                                            )}
                                            aria-describedby={
                                                errors.password_confirmation
                                                    ? 'password-confirmation-error'
                                                    : undefined
                                            }
                                        />
                                        <div className="min-h-5">
                                            <InputError
                                                id="password-confirmation-error"
                                                message={
                                                    errors.password_confirmation
                                                }
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="grid gap-2">
                                    <FieldLabel htmlFor="locale" required>
                                        {t('Language')}
                                    </FieldLabel>
                                    <select
                                        id="locale"
                                        name="locale"
                                        defaultValue={
                                            user?.locale ?? locales[0]?.value
                                        }
                                        className="bg-card h-9 rounded-md border px-3 text-sm"
                                        required
                                    >
                                        {locales.map((locale) => (
                                            <option
                                                key={locale.value}
                                                value={locale.value}
                                            >
                                                {locale.label}
                                            </option>
                                        ))}
                                    </select>
                                    <InputError message={errors.locale} />
                                </div>

                                <div className="grid gap-2">
                                    <FieldLabel htmlFor="timezone" required>
                                        {t('Timezone')}
                                    </FieldLabel>
                                    <Input
                                        id="timezone"
                                        name="timezone"
                                        defaultValue={user?.timezone ?? 'UTC'}
                                        required
                                        placeholder="Asia/Ho_Chi_Minh"
                                        aria-invalid={Boolean(errors.timezone)}
                                    />
                                    <InputError message={errors.timezone} />
                                </div>
                            </div>
                        </section>

                        <aside>
                            <section className="bg-card rounded-xl border p-4 shadow-sm sm:p-5">
                                <div className="mb-5 border-b pb-4">
                                    <h2 className="text-base font-semibold">
                                        {t('Access')}
                                    </h2>
                                    <p className="text-muted-foreground mt-1 text-sm">
                                        {t('Admin profile help')}
                                    </p>
                                </div>
                                <label className="bg-muted/30 hover:bg-muted/50 flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition-colors">
                                    <input
                                        type="hidden"
                                        name="is_admin"
                                        value="0"
                                    />
                                    <input
                                        type="checkbox"
                                        id="is_admin"
                                        name="is_admin"
                                        value="1"
                                        defaultChecked={user?.is_admin ?? false}
                                        className="border-input accent-primary mt-0.5 size-4 shrink-0 rounded"
                                    />
                                    <span className="grid gap-1">
                                        <span className="text-sm font-medium">
                                            {t('Administrator')}
                                        </span>
                                        <span className="text-muted-foreground text-xs leading-5">
                                            {t(
                                                'Grants access to the admin area.',
                                            )}
                                        </span>
                                    </span>
                                </label>
                                <InputError message={errors.is_admin} />
                            </section>
                        </aside>
                    </div>

                    <AdminFormActions processing={processing} />
                </>
            )}
        </Form>
    );
}
