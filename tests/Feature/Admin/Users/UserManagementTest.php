<?php

namespace Tests\Feature\Admin\Users;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_admin_users(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_unverified_admin_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->admin()->unverified()->create())
            ->get(route('admin.users.index'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_admin_can_view_users_and_create_a_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/index')
                ->has('users.data')
                ->has('users.meta.links')
            );

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Managed User',
            'email' => 'managed@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'locale' => 'en',
            'timezone' => 'UTC',
            'is_admin' => false,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'managed@example.com', 'is_admin' => false]);
    }

    public function test_admin_list_filters_and_sorts_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Alice Example', 'email' => 'alice@example.com']);
        User::factory()->create(['name' => 'Bob Example', 'email' => 'bob@example.com']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', [
                'search' => 'alice',
                'is_admin' => '0',
                'sort' => 'name',
                'direction' => 'asc',
            ]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.email', 'alice@example.com')
                ->where('filters.search', 'alice')
                ->where('filters.is_admin', false)
                ->where('filters.sort', 'name')
                ->where('filters.direction', 'asc')
            );
    }

    public function test_admin_can_update_a_user_and_email_change_requires_verification(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['email' => 'old@example.com']);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $user), [
            'name' => 'Updated User',
            'email' => 'new@example.com',
            'locale' => 'vi',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'is_admin' => false,
        ]);

        $response->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'email' => 'new@example.com',
            'email_verified_at' => null,
            'locale' => 'vi',
        ]);
    }

    public function test_admin_edit_page_exposes_a_flat_user_resource(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $user))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/users/edit')
                ->where('user.data.id', $user->id)
            );
    }

    public function test_admin_cannot_remove_own_admin_access_or_delete_themselves(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'locale' => 'en',
                'timezone' => 'UTC',
                'is_admin' => false,
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertForbidden();
    }

    public function test_last_admin_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $target = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users.index'));

        $this->assertSoftDeleted('users', ['id' => $target->id]);

        $this->delete(route('admin.users.destroy', $admin))->assertForbidden();
    }

    public function test_admin_can_restore_and_force_delete_a_soft_deleted_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->regular()->create();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $user));
        $this->assertSoftDeleted('users', ['id' => $user->id]);

        $this->patch(route('admin.users.restore', $user->id))
            ->assertRedirect()
            ->assertSessionHas('inertia.flash_data.toast.message', __('common.user_restored'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);

        $this->delete(route('admin.users.destroy', $user));
        $this->delete(route('admin.users.force-destroy', $user->id))
            ->assertRedirect()
            ->assertSessionHas('inertia.flash_data.toast.message', __('common.user_force_deleted'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_can_bulk_soft_delete_users(): void
    {
        $admin = User::factory()->admin()->create();
        $users = User::factory()->regular()->count(2)->create();

        $this->actingAs($admin)
            ->post(route('admin.users.bulk-destroy'), ['ids' => $users->modelKeys()])
            ->assertRedirect()
            ->assertSessionHas('inertia.flash_data.toast.message', __('common.users_deleted_count', ['count' => 2]));

        foreach ($users as $user) {
            $this->assertSoftDeleted('users', ['id' => $user->id]);
        }
    }

    public function test_admin_can_bulk_restore_users(): void
    {
        $admin = User::factory()->admin()->create();
        $users = User::factory()->regular()->count(2)->create();
        $users->each->delete();

        $this->actingAs($admin)
            ->post(route('admin.users.bulk-restore'), ['ids' => $users->modelKeys()])
            ->assertRedirect()
            ->assertSessionHas('inertia.flash_data.toast.message', __('common.users_restored_count', ['count' => 2]));

        foreach ($users as $user) {
            $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
        }
    }

    public function test_admin_can_bulk_force_delete_deleted_users(): void
    {
        $admin = User::factory()->admin()->create();
        $users = User::factory()->regular()->count(2)->create();
        $users->each->delete();

        $this->actingAs($admin)
            ->post(route('admin.users.bulk-force-destroy'), ['ids' => $users->modelKeys()])
            ->assertRedirect()
            ->assertSessionHas(
                'inertia.flash_data.toast.message',
                __('common.users_force_deleted_count', ['count' => 2]),
            );

        foreach ($users as $user) {
            $this->assertDatabaseMissing('users', ['id' => $user->id]);
        }
    }

    public function test_bulk_user_requests_validate_the_ids_payload(): void
    {
        $admin = User::factory()->admin()->create();
        $deletedUser = User::factory()->regular()->create();
        $deletedUser->delete();
        $activeUser = User::factory()->regular()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.bulk-destroy'), [
                'ids' => [$deletedUser->id, $activeUser->id, $activeUser->id],
            ])
            ->assertSessionHasErrors('ids.0')
            ->assertSessionHasErrors('ids.2');

        $this->actingAs($admin)
            ->post(route('admin.users.bulk-restore'), [
                'ids' => [$activeUser->id],
            ])
            ->assertSessionHasErrors('ids.0');

        $this->actingAs($admin)
            ->post(route('admin.users.bulk-force-destroy'), [
                'ids' => [$activeUser->id],
            ])
            ->assertSessionHasErrors('ids.0');
    }

    public function test_deleted_users_are_marked_as_deleted_in_the_index_resource(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->regular()->create();
        $user->delete();

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['trashed' => 'only']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.id', $user->id)
                ->whereNotNull('users.data.0.deleted_at')
            );
    }

    public function test_deleted_users_cannot_be_edited_or_updated(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->regular()->create();
        $user->delete();

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $user->id))
            ->assertNotFound();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user->id), [
                'name' => 'Updated',
                'email' => $user->email,
                'locale' => 'en',
                'timezone' => 'UTC',
                'is_admin' => false,
            ])
            ->assertNotFound();
    }

    public function test_search_treats_percent_and_underscore_as_literal_text(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->regular()->create(['name' => '100% real']);
        User::factory()->regular()->create(['name' => '1000 real']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => '100%']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.name', '100% real')
            );
    }

    public function test_user_input_is_validated(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'locale' => 'invalid',
                'timezone' => 'Invalid/Timezone',
                'is_admin' => 'not-a-boolean',
            ])
            ->assertSessionHasErrors(['name', 'email', 'password', 'locale', 'timezone', 'is_admin']);
    }

    public function test_index_page_parameter_must_be_positive(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['page' => 0]))
            ->assertSessionHasErrors('page');
    }

    public function test_timezone_validation_message_is_translated(): void
    {
        $admin = User::factory()->admin()->create(['locale' => 'vi']);
        $user = User::factory()->regular()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'locale' => 'vi',
                'timezone' => 'Invalid/Timezone',
                'is_admin' => false,
            ])
            ->assertSessionHasErrors([
                'timezone' => 'Múi giờ được chọn không hợp lệ.',
            ]);
    }

    public function test_password_confirmation_requires_a_new_password_on_update(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->regular()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'locale' => 'en',
                'timezone' => 'UTC',
                'password' => '',
                'password_confirmation' => 'Password123!',
                'is_admin' => false,
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_deleting_last_record_on_a_page_redirects_to_the_previous_page(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->regular()->count(14)->create();
        $target = User::factory()->regular()->create([
            'created_at' => now()->subDays(2),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', [
                'user' => $target,
                'page' => 2,
                'per_page' => 15,
            ]))
            ->assertRedirect(route('admin.users.index', [
                'per_page' => 15,
                'page' => 2,
            ]));

        $this->get(route('admin.users.index', [
            'per_page' => 15,
            'page' => 2,
        ]))->assertRedirect(route('admin.users.index', [
            'per_page' => 15,
            'page' => 1,
        ]));
    }
}
