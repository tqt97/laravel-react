import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import UserController from '@/actions/App/Http/Controllers/Admin/Users/UserController';
import { UserForm } from '@/components/admin/users/user-form';
import { PageHeader } from '@/components/admin/page-header';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';
import type { AdminUser } from '@/types/admin/users';

type UserResource = { data: AdminUser };

export default function EditUser({
    user: userResource,
}: {
    user: UserResource;
}) {
    const { t } = useTranslation();
    const user = userResource.data;

    return (
        <>
            <Head title={t('Edit user')} />
            <PageHeader
                eyebrow={t('Administration')}
                title={t('Edit user')}
                description={t(
                    'Update the account details and access for :email.',
                    {
                        email: user.email,
                    },
                )}
                actions={
                    <Button asChild variant="outline">
                        <Link href={UserController.index()}>
                            <ArrowLeft aria-hidden="true" />
                            {t('Cancel')}
                        </Link>
                    </Button>
                }
            />
            <div className="bg-card rounded-xl border p-4 shadow-sm sm:p-6">
                <UserForm
                    action={UserController.update(user.id).url}
                    method="put"
                    user={user}
                />
            </div>
        </>
    );
}
