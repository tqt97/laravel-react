<?php

namespace App\Actions\Admin\Users;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class BulkRestoreUsers
{
    /**
     * @param  array<int, int>  $ids
     */
    public function execute(User $actor, array $ids): int
    {
        if (! $actor->is_admin) {
            throw new AuthorizationException(__('authorization.denial.insufficient_permission'));
        }

        return DB::transaction(function () use ($ids): int {
            $users = User::onlyTrashed()->whereKey($ids)->lockForUpdate()->get();

            if ($users->count() !== count(array_unique($ids))) {
                throw new AuthorizationException(__('authorization.denial.selected_users_cannot_restore'));
            }

            $users->each->restore();

            return $users->count();
        });
    }
}
