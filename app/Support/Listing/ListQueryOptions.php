<?php

namespace App\Support\Listing;

final readonly class ListQueryOptions
{
    /**
     * @param  array<string, string>  $sortableColumns
     */
    public function __construct(
        public array $sortableColumns,
        public string $sort,
        public string $direction,
        public int $perPage,
        public string $defaultSort = 'created_at',
        public string $tieBreaker = 'id',
    ) {}
}
