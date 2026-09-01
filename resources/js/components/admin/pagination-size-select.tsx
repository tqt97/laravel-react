import { useTranslation } from '@/hooks/use-translation';
import { PAGINATION_SIZES } from '@/lib/pagination';

export function PaginationSizeSelect({
    value,
    onChange,
}: {
    value: number;
    onChange: (value: number) => void;
}) {
    const { t } = useTranslation();

    return (
        <div className="text-muted-foreground flex items-center justify-end gap-2 text-sm">
            <label htmlFor="pagination-size" className="font-medium">
                {t('Show')}
            </label>
            <select
                id="pagination-size"
                value={value}
                onChange={(event) => onChange(Number(event.target.value))}
                className="bg-card text-foreground h-9 cursor-pointer rounded-md border px-2 shadow-sm"
            >
                {PAGINATION_SIZES.map((size) => (
                    <option key={size} value={size}>
                        {size} {t('items')}
                    </option>
                ))}
            </select>
        </div>
    );
}
