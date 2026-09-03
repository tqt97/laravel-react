<?php

namespace App\Actions\Admin\Users;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class BulkForceDeleteUsers
{
    /**
     * @param  array<int, int>  $ids
     */
    public function execute(User $actor, array $ids): int
    {
        if (! $actor->can('forceDeleteAny', User::class)) {
            throw new AuthorizationException(__('authorization.denial.insufficient_permission'));
        }

        return DB::transaction(function () use ($actor, $ids): int {
            $users = User::onlyTrashed()->whereKey($ids)->lockForUpdate()->get();

            if ($users->count() !== count(array_unique($ids))) {
                throw new AuthorizationException(__('authorization.denial.selected_users_cannot_force_delete'));
            }

            foreach ($users as $user) {
                if ($actor->is($user)) {
                    throw new AuthorizationException(__('authorization.denial.self_target'));
                }
            }

            $users->each->forceDelete();

            return $users->count();
        });
    }
}
