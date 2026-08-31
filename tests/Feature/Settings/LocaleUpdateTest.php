<?php

namespace Tests\Feature\Settings;

use App\Enums\Locale;
use App\Models\User;
use App\Support\Locale\LocaleResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_guest_uses_supported_accept_language_when_no_preference_exists(): void
    {
        $this->withHeader('Accept-Language', 'vi-VN, en;q=0.8')
            ->get(route('home'))
            ->assertOk();

        $this->assertSame(Locale::VIETNAMESE->value, app()->getLocale());
    }

    public function test_guest_can_disable_accept_language_resolution(): void
    {
        config(['locale.accept_language' => false]);

        $this->withHeader('Accept-Language', 'vi-VN')
            ->get(route('home'))
            ->assertOk();

        $this->assertSame(Locale::ENGLISH->value, app()->getLocale());
    }

    public function test_guest_uses_configured_fallback_locale(): void
    {
        config(['app.fallback_locale' => Locale::VIETNAMESE->value]);

        $this->withHeader('Accept-Language', 'fr-FR')
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

    public function test_authenticated_user_without_saved_locale_uses_session_locale(): void
    {
        $user = User::factory()->make(['locale' => null]);
        $request = Request::create('/');
        $request->setUserResolver(fn (): User => $user);
        $request->setLaravelSession(session()->driver());
        $request->session()->put('locale', Locale::VIETNAMESE->value);

        $this->assertSame(
            Locale::VIETNAMESE,
            app(LocaleResolver::class)->resolve($request),
        );
    }

    public function test_inertia_shares_locale_timezone_and_translations(): void
    {
        $user = User::factory()->create([
            'locale' => Locale::VIETNAMESE,
            'timezone' => 'Asia/Ho_Chi_Minh',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('locale', Locale::VIETNAMESE->value)
                ->where('timezone', 'Asia/Ho_Chi_Minh')
                ->has('translations'));
    }

    public function test_invalid_user_timezone_falls_back_to_application_timezone(): void
    {
        $user = User::factory()->create(['timezone' => 'Invalid/Timezone']);

        $this->assertSame(config('app.timezone'), $user->preferredTimezone());
    }

    public function test_invalid_application_timezone_falls_back_to_utc(): void
    {
        config(['app.timezone' => 'Invalid/Timezone']);

        $user = User::factory()->create(['timezone' => 'Invalid/Timezone']);

        $this->assertSame('UTC', $user->preferredTimezone());
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
