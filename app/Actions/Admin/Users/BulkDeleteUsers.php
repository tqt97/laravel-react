<?php

namespace App\Actions\Admin\Users;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class BulkDeleteUsers
{
    /**
     * @param  array<int, int>  $ids
     */
    public function execute(User $actor, array $ids): int
    {
        if (! $actor->is_admin) {
            throw new AuthorizationException(__('authorization.denial.insufficient_permission'));
        }

        return DB::transaction(function () use ($actor, $ids): int {
            $users = User::query()->whereKey($ids)->lockForUpdate()->get();
            $adminCount = User::administrators()->lockForUpdate()->count();

            if ($users->count() !== count(array_unique($ids))) {
                throw new AuthorizationException(__('authorization.denial.selected_users_missing'));
            }

            foreach ($users as $user) {
                if ($actor->is($user) || ($user->is_admin && $adminCount <= 1)) {
                    throw new AuthorizationException(__('authorization.denial.selected_users_cannot_delete'));
                }

                if ($user->is_admin) {
                    $adminCount--;
                }
            }

            $users->each->delete();

            return $users->count();
        });
    }
}
