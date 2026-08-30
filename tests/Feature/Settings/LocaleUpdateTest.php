<?php

namespace Tests\Feature\Settings;

use App\Enums\Locale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_update_locale_in_the_session()
    {
        $response = $this->patch(route('locale.update'), [
            'locale' => Locale::VIETNAMESE->value,
        ]);

        $response
            ->assertRedirect()
            ->assertSessionHas('locale', Locale::VIETNAMESE->value);
    }

    public function test_authenticated_user_can_update_locale()
    {
        $user = User::factory()->create([
            'locale' => Locale::ENGLISH,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('locale.update'), [
                'locale' => Locale::VIETNAMESE->value,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => Locale::VIETNAMESE->value,
        ]);
    }

    public function test_unsupported_locale_is_rejected()
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

    public function test_public_pages_use_a_safe_fallback_locale()
    {
        $response = $this
            ->withSession(['locale' => 'invalid'])
            ->get(route('home'));

        $response->assertOk();
        $this->assertSame(Locale::ENGLISH->value, app()->getLocale());
    }
}
