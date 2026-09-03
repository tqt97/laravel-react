<?php

namespace App\Http\Requests\Admin\Users;

use App\Enums\Locale;
use App\Models\User;
use App\Queries\Admin\Users\UserIndexSort;
use App\Support\Listing\ListFilterRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', User::class) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'is_admin' => ['nullable', 'boolean'],
            'locale' => ['nullable', new Enum(Locale::class)],
            'timezone' => ['nullable', 'string', 'max:64', Rule::in(timezone_identifiers_list())],
            'trashed' => ListFilterRules::trashed(),
            'sort' => ['nullable', Rule::in(UserIndexSort::values())],
            'direction' => ListFilterRules::direction(),
            'per_page' => ListFilterRules::perPage(),
            'page' => ListFilterRules::page(),
        ];
    }
}
