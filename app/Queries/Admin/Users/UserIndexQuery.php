<?php

namespace App\Queries\Admin\Users;

use App\DTOs\Admin\Users\UserFilterDTO;
use App\Models\User;
use App\Support\Listing\ListQuery;
use App\Support\Listing\ListQueryOptions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class UserIndexQuery
{
    public function __construct(private readonly ListQuery $listQuery) {}

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function execute(UserFilterDTO $filters): LengthAwarePaginator
    {
        $query = match ($filters->trashed) {
            'with' => User::withTrashed(),
            'only' => User::onlyTrashed(),
            default => User::query(),
        };

        $query = $query
            ->select([
                'id', 'name', 'email', 'is_admin', 'locale', 'timezone',
                'email_verified_at', 'created_at', 'deleted_at',
            ])
            ->when($filters->search, function (Builder $query, string $search): void {
                $search = self::escapeLike($search);

                $query->where(function (Builder $query) use ($search): void {
                    $query->whereRaw("name LIKE ? ESCAPE '\\'", ["%{$search}%"])
                        ->orWhereRaw("email LIKE ? ESCAPE '\\'", ["%{$search}%"]);
                });
            })
            ->when($filters->isAdmin !== null, fn (Builder $query): Builder => $query->where('is_admin', $filters->isAdmin))
            ->when($filters->locale !== null, fn (Builder $query): Builder => $query->where('locale', $filters->locale->value))
            ->when($filters->timezone, fn (Builder $query, string $timezone): Builder => $query->where('timezone', $timezone));

        return $this->listQuery->paginate(
            query: $query,
            options: new ListQueryOptions(
                sortableColumns: UserIndexSort::columns(),
                sort: $filters->sort,
                direction: $filters->direction,
                perPage: $filters->perPage,
            ),
        );
    }

    private static function escapeLike(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value,
        );
    }
}
