<?php

namespace App\Http\Requests\Admin\Users;

use App\Enums\Locale;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && ($this->user()?->can('update', $user) ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $email = Rule::unique('users', 'email');

        if ($user instanceof User) {
            $email->ignore($user);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $email],
            'password' => [
                'nullable',
                'required_with:password_confirmation',
                'confirmed',
                Password::defaults(),
            ],
            'locale' => ['required', new Enum(Locale::class)],
            'timezone' => ['required', 'string', 'max:64', Rule::in(timezone_identifiers_list())],
            'is_admin' => ['required', 'boolean'],
        ];
    }
}
