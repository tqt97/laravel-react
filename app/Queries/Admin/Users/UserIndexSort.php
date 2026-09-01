<?php

namespace App\Queries\Admin\Users;

final class UserIndexSort
{
    /**
     * @return array<string, string>
     */
    public static function columns(): array
    {
        return [
            'name' => 'name',
            'email' => 'email',
            'is_admin' => 'is_admin',
            'locale' => 'locale',
            'timezone' => 'timezone',
            'created_at' => 'created_at',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_keys(self::columns());
    }
}
