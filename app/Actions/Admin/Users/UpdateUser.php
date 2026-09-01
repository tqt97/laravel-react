<?php

namespace App\Actions\Admin\Users;

use App\DTOs\Admin\Users\UserDTO;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class UpdateUser
{
    public function execute(User $actor, User $user, UserDTO $data): User
    {
        if ($actor->is($user) && ! $data->isAdmin) {
            throw new AuthorizationException(__('authorization.denial.self_target'));
        }

        return DB::transaction(function () use ($user, $data): User {
            $lockedUser = User::query()
                ->lockForUpdate()
                ->whereKey($user->getKey())
                ->firstOrFail();

            if ($lockedUser->is_admin && ! $data->isAdmin
                && User::administrators()->lockForUpdate()->count() <= 1) {
                throw new AuthorizationException(__('authorization.denial.last_admin'));
            }

            $lockedUser->fill([
                'name' => $data->name,
                'email' => $data->email,
                'locale' => $data->locale,
                'timezone' => $data->timezone,
                'is_admin' => $data->isAdmin,
            ]);

            if ($data->password !== null) {
                $lockedUser->password = $data->password;
            }

            if ($lockedUser->isDirty('email')) {
                $lockedUser->email_verified_at = null;
            }

            $lockedUser->save();

            return $lockedUser->refresh();
        });
    }
}
