import { Plus } from 'lucide-react';
import type { ComponentProps } from 'react';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { useTranslation } from '@/hooks/use-translation';

export function AddNewButton({
    href,
    label,
    ...props
}: Omit<ComponentProps<typeof Button>, 'asChild' | 'children'> & {
    href: NonNullable<ComponentProps<typeof Link>>['href'];
    label?: string;
}) {
    const { t } = useTranslation();

    return (
        <Button asChild {...props}>
            <Link href={href} prefetch="hover">
                <Plus aria-hidden="true" />
                {label ?? t('Add new')}
            </Link>
        </Button>
    );
}
