<?php

namespace App\Actions\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ForceDeleteUser
{
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user): void {
            User::withTrashed()
                ->whereKey($user->getKey())
                ->whereNotNull('deleted_at')
                ->lockForUpdate()
                ->firstOrFail()
                ->forceDelete();
        });
    }
}
