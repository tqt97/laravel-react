<?php

namespace App\Actions\Admin\Users;

use App\DTOs\Admin\Users\UserDTO;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateUser
{
    public function execute(UserDTO $data): User
    {
        return DB::transaction(fn (): User => User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'locale' => $data->locale,
            'timezone' => $data->timezone,
            'is_admin' => $data->isAdmin,
        ]));
    }
}
