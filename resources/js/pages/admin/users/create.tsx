import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import UserController from '@/actions/App/Http/Controllers/Admin/Users/UserController';
import { UserForm } from '@/components/admin/users/user-form';
import { PageHeader } from '@/components/admin/page-header';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';

export default function CreateUser() {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('Create user')} />
            <PageHeader
                title={t('Create user')}
                description={t('Add user description')}
                actions={
                    <Button asChild variant="outline">
                        <Link href={UserController.index()}>
                            <ArrowLeft aria-hidden="true" />
                            {t('Cancel')}
                        </Link>
                    </Button>
                }
            />
            <UserForm action={UserController.store.url()} method="post" />
        </>
    );
}
