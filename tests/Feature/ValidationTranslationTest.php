<?php

namespace Tests\Feature;

use App\Enums\Locale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_validation_uses_the_authenticated_user_locale(): void
    {
        $user = User::factory()->create(['locale' => Locale::VIETNAMESE]);

        $response = $this
            ->actingAs($user)
            ->patch(route('profile.update'), [
                'email' => 'invalid-email',
            ]);

        $response->assertSessionHasErrors([
            'name' => 'Trường tên là bắt buộc.',
            'email' => 'địa chỉ email phải là địa chỉ email hợp lệ.',
        ]);
    }

    public function test_password_validation_uses_nested_vietnamese_messages(): void
    {
        $user = User::factory()->create(['locale' => Locale::VIETNAMESE]);

        $response = $this
            ->actingAs($user)
            ->put(route('user-password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'different-password',
            ]);

        $response->assertSessionHasErrors([
            'current_password' => 'Mật khẩu không chính xác.',
            'password' => 'Xác nhận mật khẩu không khớp.',
        ]);
    }

    public function test_locale_validation_uses_vietnamese_messages(): void
    {
        $user = User::factory()->create(['locale' => Locale::VIETNAMESE]);

        $response = $this
            ->actingAs($user)
            ->patch(route('locale.update'), ['locale' => 'fr']);

        $response->assertSessionHasErrors([
            'locale' => 'ngôn ngữ được chọn không hợp lệ.',
        ]);
    }
}
