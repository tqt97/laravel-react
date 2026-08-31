<?php

namespace Tests\Feature\Settings;

use App\Enums\Locale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_update_locale(): void
    {
        $response = $this->patch(route('locale.update'), [
            'locale' => Locale::VIETNAMESE->value,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_update_locale(): void
    {
        $user = User::factory()->create([
            'locale' => Locale::ENGLISH,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('locale.update'), [
                'locale' => Locale::VIETNAMESE->value,
            ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('inertia.flash_data.toast.message', 'Đã cập nhật ngôn ngữ.');
        $response->assertPlainCookie(config('locale.cookie.name'), Locale::VIETNAMESE->value);
        $response->assertSessionHas('locale', Locale::VIETNAMESE->value);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => Locale::VIETNAMESE->value,
        ]);
    }

    public function test_guest_uses_locale_cookie(): void
    {
        $this->withUnencryptedCookie(config('locale.cookie.name'), Locale::VIETNAMESE->value)
            ->get(route('home'))
            ->assertOk();

        $this->assertSame(Locale::VIETNAMESE->value, app()->getLocale());
    }

    public function test_authenticated_user_locale_takes_precedence_over_session(): void
    {
        $user = User::factory()->create(['locale' => Locale::VIETNAMESE]);

        $this->actingAs($user)->withSession(['locale' => Locale::ENGLISH->value])
            ->get(route('dashboard'));

        $this->assertSame(Locale::VIETNAMESE->value, app()->getLocale());
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $user = User::factory()->create([
            'locale' => Locale::ENGLISH,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('locale.update'), [
                'locale' => 'fr',
            ]);

        $response->assertSessionHasErrors('locale');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => Locale::ENGLISH->value,
        ]);
    }

    public function test_public_pages_use_a_safe_fallback_locale(): void
    {
        $response = $this
            ->withSession(['locale' => 'invalid'])
            ->get(route('home'));

        $response->assertOk();
        $this->assertSame(Locale::ENGLISH->value, app()->getLocale());
    }
}
