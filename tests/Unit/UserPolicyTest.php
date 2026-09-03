<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_use_any_user_ability(): void
    {
        $actor = User::factory()->regular()->create();
        $target = User::factory()->regular()->create();
        $policy = new UserPolicy;

        $this->assertFalse($policy->viewAny($actor));
        $this->assertFalse($policy->deleteAny($actor));
        $this->assertFalse($policy->restoreAny($actor));
        $this->assertFalse($policy->forceDeleteAny($actor));
        $this->assertFalse($policy->view($actor, $target));
        $this->assertFalse($policy->create($actor));
        $this->assertFalse($policy->update($actor, $target));
        $this->assertFalse($policy->delete($actor, $target));
        $this->assertFalse($policy->restore($actor, $target));
        $this->assertFalse($policy->forceDelete($actor, $target));
    }

    public function test_admin_can_view_create_and_update_users(): void
    {
        $actor = User::factory()->admin()->create();
        $target = User::factory()->regular()->create();
        $policy = new UserPolicy;

        $this->assertTrue($policy->viewAny($actor));
        $this->assertTrue($policy->deleteAny($actor));
        $this->assertTrue($policy->restoreAny($actor));
        $this->assertTrue($policy->forceDeleteAny($actor));
        $this->assertTrue($policy->view($actor, $target));
        $this->assertTrue($policy->create($actor));
        $this->assertTrue($policy->update($actor, $target));
    }

    public function test_admin_can_delete_regular_users_but_not_themselves(): void
    {
        $actor = User::factory()->admin()->create();
        $target = User::factory()->regular()->create();
        $policy = new UserPolicy;

        $this->assertTrue($policy->delete($actor, $target));
        $this->assertFalse($policy->delete($actor, $actor));
    }

    public function test_last_admin_is_protected_but_multiple_admins_can_be_deleted(): void
    {
        $actor = User::factory()->admin()->create();
        $lastAdmin = User::factory()->admin()->create();
        $policy = new UserPolicy;

        $this->assertTrue($policy->delete($actor, $lastAdmin));
        $lastAdmin->delete();
        $this->assertFalse($policy->delete($actor, $actor));
    }

    public function test_restore_and_force_delete_require_a_trashed_target(): void
    {
        $actor = User::factory()->admin()->create();
        $active = User::factory()->regular()->create();
        $deleted = User::factory()->regular()->create();
        $deleted->delete();
        $policy = new UserPolicy;

        $this->assertFalse($policy->restore($actor, $active));
        $this->assertFalse($policy->forceDelete($actor, $active));
        $this->assertTrue($policy->restore($actor, $deleted));
        $this->assertTrue($policy->forceDelete($actor, $deleted));
    }
}
