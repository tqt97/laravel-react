import { Eye, EyeOff } from 'lucide-react';
import type { ComponentProps, Ref } from 'react';
import { useState } from 'react';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { useTranslation } from '@/hooks/use-translation';

export default function PasswordInput({
    className,
    ref,
    ...props
}: Omit<ComponentProps<'input'>, 'type'> & { ref?: Ref<HTMLInputElement> }) {
    const [showPassword, setShowPassword] = useState(false);
    const { t } = useTranslation();

    return (
        <div className="relative">
            <Input
                type={showPassword ? 'text' : 'password'}
                className={cn('pr-10', className)}
                ref={ref}
                {...props}
            />
            <button
                type="button"
                onClick={() => setShowPassword((prev) => !prev)}
                className="text-muted-foreground hover:text-foreground focus-visible:ring-ring absolute inset-y-0 right-0 flex items-center rounded-r-md px-3 hover:cursor-pointer focus-visible:ring-[3px] focus-visible:outline-none"
                aria-label={
                    showPassword ? t('Hide password') : t('Show password')
                }
            >
                {showPassword ? (
                    <EyeOff aria-hidden="true" className="size-4" />
                ) : (
                    <Eye aria-hidden="true" className="size-4" />
                )}
            </button>
        </div>
    );
}
