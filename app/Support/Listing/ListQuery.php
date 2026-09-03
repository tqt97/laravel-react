<?php

namespace App\Support\Listing;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ListQuery
{
    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return LengthAwarePaginator<int, TModel>
     */
    public function paginate(Builder $query, ListQueryOptions $options): LengthAwarePaginator
    {
        $sortColumn = $options->sortableColumns[$options->sort]
            ?? $options->sortableColumns[$options->defaultSort]
            ?? $options->defaultSort;
        $sortDirection = in_array($options->direction, ListFilterRules::DIRECTIONS, true)
            ? $options->direction
            : 'desc';

        return $query
            ->orderBy($sortColumn, $sortDirection)
            ->orderBy($options->tieBreaker, 'desc')
            ->paginate($options->perPage)
            ->withQueryString();
    }
}
