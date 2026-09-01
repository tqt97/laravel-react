<?php

namespace App\Support\Listing;

use Illuminate\Validation\Rule;

final class ListFilterRules
{
    public const DEFAULT_PER_PAGE = 15;

    /** @var array<int, string> */
    public const TRASHED_VALUES = ['without', 'with', 'only'];

    /** @var array<int, int> */
    public const PER_PAGE_VALUES = [15, 25, 50, 100];

    /** @var array<int, string> */
    public const DIRECTIONS = ['asc', 'desc'];

    /**
     * @return array<int, mixed>
     */
    public static function trashed(): array
    {
        return ['nullable', Rule::in(self::TRASHED_VALUES)];
    }

    /**
     * @return array<int, mixed>
     */
    public static function perPage(): array
    {
        return ['nullable', 'integer', Rule::in(self::PER_PAGE_VALUES)];
    }

    /**
     * @return array<int, mixed>
     */
    public static function direction(): array
    {
        return ['nullable', Rule::in(self::DIRECTIONS)];
    }

    /**
     * @return array<int, mixed>
     */
    public static function page(): array
    {
        return ['nullable', 'integer', 'min:1'];
    }
}
