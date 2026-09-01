<?php

namespace App\Actions\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RestoreUser
{
    public function execute(User $user): User
    {
        return DB::transaction(function () use ($user): User {
            $user = User::withTrashed()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $user->restore();

            return $user->refresh();
        });
    }
}
