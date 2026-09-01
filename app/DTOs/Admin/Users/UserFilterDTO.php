<?php

namespace App\DTOs\Admin\Users;

use App\Enums\Locale;
use App\Http\Requests\Admin\Users\UserIndexRequest;
use App\Support\Listing\ListFilterRules;

final readonly class UserFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public ?bool $isAdmin = null,
        public ?Locale $locale = null,
        public ?string $timezone = null,
        public string $trashed = 'without',
        public string $sort = 'created_at',
        public string $direction = 'desc',
        public int $perPage = ListFilterRules::DEFAULT_PER_PAGE,
    ) {}

    public static function fromRequest(UserIndexRequest $request): self
    {
        $validated = $request->validated();
        $isAdmin = $validated['is_admin'] ?? null;

        return new self(
            search: filled($validated['search'] ?? null)
                ? trim((string) $validated['search'])
                : null,
            isAdmin: $isAdmin === null || $isAdmin === ''
                ? null
                : (bool) $isAdmin,
            locale: filled($validated['locale'] ?? null)
                ? Locale::from((string) $validated['locale'])
                : null,
            timezone: filled($validated['timezone'] ?? null)
                ? (string) $validated['timezone']
                : null,
            trashed: (string) ($validated['trashed'] ?? 'without'),
            sort: (string) ($validated['sort'] ?? 'created_at'),
            direction: (string) ($validated['direction'] ?? 'desc'),
            perPage: (int) ($validated['per_page'] ?? ListFilterRules::DEFAULT_PER_PAGE),
        );
    }
}
