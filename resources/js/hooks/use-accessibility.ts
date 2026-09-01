import { router } from '@inertiajs/react';
import { useEffect } from 'react';

type ValidationErrors = Record<string, unknown>;

function findField(
    fields: HTMLElement[],
    key: string,
): HTMLElement | undefined {
    const exactMatch = fields.find(
        (field) => field.getAttribute('name') === key,
    );

    if (exactMatch) {
        return exactMatch;
    }

    const normalizedKey = key
        .replace(/\[(\w+)\]/g, '.$1')
        .split('.')
        .pop();

    return fields.find((field) => {
        const name = field.getAttribute('name')?.replace(/\[(\w+)\]/g, '.$1');

        return name?.split('.').pop() === normalizedKey;
    });
}

function focusFirstInvalidField(errors: ValidationErrors): void {
    const fields = Array.from(
        document.querySelectorAll<HTMLElement>(
            'input[name], select[name], textarea[name], [contenteditable="true"]',
        ),
    );

    const firstField = Object.keys(errors)
        .map((key) => findField(fields, key))
        .find((field): field is HTMLElement => field !== undefined);

    firstField?.focus({ preventScroll: false });
}

export function useAccessibility(): void {
    useEffect(() => {
        const focusMainContent = (): void => {
            window.requestAnimationFrame(() => {
                document.getElementById('main-content')?.focus({
                    preventScroll: true,
                });
            });
        };

        const removeNavigateListener = router.on('navigate', focusMainContent);
        const removeErrorListener = router.on('error', (event) => {
            const errors = event.detail.errors as ValidationErrors;

            if (!errors || Object.keys(errors).length === 0) {
                return;
            }

            window.requestAnimationFrame(() => focusFirstInvalidField(errors));
        });

        return () => {
            removeNavigateListener();
            removeErrorListener();
        };
    }, []);
}
