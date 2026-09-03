<?php

namespace App\Actions\Admin\Users;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class DeleteUser
{
    public function execute(User $actor, User $user): void
    {
        if ($actor->is($user)) {
            throw new AuthorizationException(__('authorization.denial.self_target'));
        }

        DB::transaction(function () use ($user): void {
            $lockedUser = User::query()
                ->lockForUpdate()
                ->whereKey($user->getKey())
                ->firstOrFail();

            if ($lockedUser->is_admin && User::administrators()->lockForUpdate()->count() <= 1) {
                throw new AuthorizationException(__('authorization.denial.last_admin'));
            }

            $lockedUser->delete();
        });
    }
}
