<?php

namespace App\DTOs\Admin\Users;

use App\Enums\Locale;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;

final readonly class UserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public Locale $locale,
        public string $timezone,
        public bool $isAdmin,
        public ?string $password,
    ) {}

    public static function fromRequest(StoreUserRequest|UpdateUserRequest $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            locale: Locale::from($request->string('locale')->toString()),
            timezone: $request->string('timezone')->toString(),
            isAdmin: $request->boolean('is_admin'),
            password: $request->filled('password')
                ? $request->string('password')->toString()
                : null,
        );
    }
}
