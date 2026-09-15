<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_with_canonical_slug_has_matching_role_logic(): void
    {
        $role = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Admin']
        );

        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'Aktif',
        ]);

        $this->assertTrue(Route::has('admin.dashboard'));
        $this->assertTrue($user->isSuperAdmin());
        $this->assertSame('super-admin', User::normalizeRoleSlug($role->slug));
    }

    public function test_legacy_superadmin_slug_is_normalized_for_access_checks(): void
    {
        $role = Role::firstOrCreate(
            ['slug' => 'superadmin'],
            ['name' => 'Super Admin', 'description' => 'Legacy admin role']
        );

        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'Aktif',
        ]);

        $this->assertTrue(Route::has('admin.dashboard'));
        $this->assertSame('super-admin', User::normalizeRoleSlug($role->slug));
        $this->assertTrue($user->isSuperAdmin());
    }
}
