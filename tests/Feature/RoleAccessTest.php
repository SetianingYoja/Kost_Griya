<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createRoleWithPermissions(string $name, string $slug, array $permissionSlugs = []): Role
    {
        $role = Role::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'description' => $name]
        );

        $permissionIds = [];
        foreach ($permissionSlugs as $pSlug) {
            $perm = Permission::firstOrCreate(
                ['slug' => $pSlug],
                ['name' => $pSlug, 'module' => 'General', 'description' => $pSlug]
            );
            $permissionIds[] = $perm->id;
        }

        if (! empty($permissionIds)) {
            $role->permissions()->sync($permissionIds);
        }

        return $role;
    }

    public function test_super_admin_has_canonical_role_and_full_system_access(): void
    {
        $role = $this->createRoleWithPermissions('Super Admin', 'super-admin');
        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'Aktif',
        ]);

        $this->assertTrue(Route::has('admin.dashboard'));
        $this->assertTrue($user->isSuperAdmin());
        $this->assertSame('super-admin', User::normalizeRoleSlug($role->slug));

        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertOk();

        // Super Admin juga dapat mengakses area operasional
        $responseOperasional = $this->actingAs($user)->get(route('pengelola.dashboard'));
        $responseOperasional->assertOk();
    }

    public function test_pengelola_can_access_operational_dashboard_but_blocked_from_admin(): void
    {
        $role = $this->createRoleWithPermissions('Pengelola', 'pengelola', [
            'view-kamar',
            'manage-kamar',
            'validate-booking',
            'validate-pembayaran',
            'manage-tagihan',
            'view-laporan-operasional',
        ]);

        $pengelola = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'Aktif',
        ]);

        $this->assertTrue($pengelola->isPengelola());
        $this->assertFalse($pengelola->isPemilikKost());
        $this->assertSame('pengelola', User::normalizeRoleSlug('manager'));
        $this->assertSame('pengelola', User::normalizeRoleSlug($role->slug));

        // Pengelola membuka dashboard operasional pengelola
        $response = $this->actingAs($pengelola)->get(route('pengelola.dashboard'));
        $response->assertOk();

        // Pengelola dilarang masuk ke panel Super Admin
        $forbidden = $this->actingAs($pengelola)->get(route('admin.dashboard'));
        $forbidden->assertForbidden();
    }

    public function test_pemilik_kost_has_read_only_access_and_blocked_from_operational_mutation(): void
    {
        $role = $this->createRoleWithPermissions('Pemilik Kost', 'pemilik-kost', [
            'view-kamar',
            'view-penghuni-readonly',
            'view-laporan-operasional',
        ]);

        $pemilikKost = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'Aktif',
        ]);

        $this->assertTrue($pemilikKost->isPemilikKost());
        $this->assertFalse($pemilikKost->isPengelola());

        // Pemilik kost bisa mengakses dashboard monitoring miliknya
        $responseDashboard = $this->actingAs($pemilikKost)->get(route('pemilik-kost.dashboard'));
        $responseDashboard->assertOk();

        // Pemilik kost bisa melihat data kamar read-only
        $responseKamar = $this->actingAs($pemilikKost)->get(route('pemilik-kost.kamar.index'));
        $responseKamar->assertOk();

        // Pemilik kost bisa melihat laporan
        $responseLaporan = $this->actingAs($pemilikKost)->get(route('pemilik-kost.laporan.index'));
        $responseLaporan->assertOk();

        // Pembatasan backend: Pemilik kost DILARANG mengakses dashboard operasional pengelola
        $blockedOperational = $this->actingAs($pemilikKost)->get(route('pengelola.dashboard'));
        $blockedOperational->assertForbidden();

        // Pembatasan backend: Pemilik kost DILARANG mengakses endpoint mutasi kamar create/store
        $blockedKamarCreate = $this->actingAs($pemilikKost)->get(route('pengelola.kamar.create'));
        $blockedKamarCreate->assertForbidden();

        // Pemilik kost DILARANG masuk ke Super Admin
        $blockedAdmin = $this->actingAs($pemilikKost)->get(route('admin.dashboard'));
        $blockedAdmin->assertForbidden();
    }

    public function test_penghuni_access_is_isolated_to_penghuni_dashboard(): void
    {
        $role = $this->createRoleWithPermissions('Penghuni', 'penghuni', [
            'view-kamar',
            'view-self-profile',
            'create-booking',
            'create-pembayaran',
            'view-self-tagihan',
        ]);

        $penghuni = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'Aktif',
        ]);

        $this->assertTrue($penghuni->isPenghuni());
        $this->assertFalse($penghuni->isPengelola());
        $this->assertFalse($penghuni->isPemilikKost());

        // Akses dashboard penghuni berhasil
        $response = $this->actingAs($penghuni)->get(route('penghuni.dashboard'));
        $response->assertOk();

        // Terisolasi dari admin, operasional, dan pemilik-kost
        $this->actingAs($penghuni)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($penghuni)->get(route('pengelola.dashboard'))->assertForbidden();
        $this->actingAs($penghuni)->get(route('pemilik-kost.dashboard'))->assertForbidden();
    }

    public function test_auth_controller_redirects_each_role_to_its_respective_dashboard(): void
    {
        $adminRole = $this->createRoleWithPermissions('Super Admin', 'super-admin');
        $pengelolaRole = $this->createRoleWithPermissions('Pengelola', 'pengelola');
        $pemilikRole = $this->createRoleWithPermissions('Pemilik Kost', 'pemilik-kost');
        $penghuniRole = $this->createRoleWithPermissions('Penghuni', 'penghuni');

        $admin = User::factory()->make(['role_id' => $adminRole->id]);
        $admin->setRelation('role', $adminRole);

        $pengelola = User::factory()->make(['role_id' => $pengelolaRole->id]);
        $pengelola->setRelation('role', $pengelolaRole);

        $pemilik = User::factory()->make(['role_id' => $pemilikRole->id]);
        $pemilik->setRelation('role', $pemilikRole);

        $penghuni = User::factory()->make(['role_id' => $penghuniRole->id]);
        $penghuni->setRelation('role', $penghuniRole);

        $authController = app(AuthController::class);

        $adminRedirect = $authController->redirectBasedOnRole($admin);
        $this->assertSame(route('admin.dashboard'), $adminRedirect->getTargetUrl());

        $pengelolaRedirect = $authController->redirectBasedOnRole($pengelola);
        $this->assertSame(route('pengelola.dashboard'), $pengelolaRedirect->getTargetUrl());

        $pemilikRedirect = $authController->redirectBasedOnRole($pemilik);
        $this->assertSame(route('pemilik-kost.dashboard'), $pemilikRedirect->getTargetUrl());

        $penghuniRedirect = $authController->redirectBasedOnRole($penghuni);
        $this->assertSame(route('penghuni.dashboard'), $penghuniRedirect->getTargetUrl());
    }

    public function test_user_management_displays_correct_role_badges_for_all_roles(): void
    {
        $adminRole = $this->createRoleWithPermissions('Super Admin', 'super-admin');
        $pengelolaRole = $this->createRoleWithPermissions('Pengelola', 'pengelola');
        $pemilikRole = $this->createRoleWithPermissions('Pemilik Kost', 'pemilik-kost');
        $penghuniRole = $this->createRoleWithPermissions('Penghuni', 'penghuni');

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'status' => 'Aktif',
            'name' => 'Admin User',
        ]);
        User::factory()->create([
            'role_id' => $pengelolaRole->id,
            'status' => 'Aktif',
            'name' => 'Pengelola User',
        ]);
        User::factory()->create([
            'role_id' => $pemilikRole->id,
            'status' => 'Aktif',
            'name' => 'Pemilik User',
        ]);
        User::factory()->create([
            'role_id' => $penghuniRole->id,
            'status' => 'Aktif',
            'name' => 'Penghuni User',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertOk();
        $response->assertSee('<span class="badge bg-danger">Super Admin</span>', false);
        $response->assertSee('<span class="badge bg-primary">Pemilik Kost</span>', false);
        $response->assertSee('<span class="badge bg-warning text-dark">Pengelola</span>', false);
        $response->assertSee('<span class="badge bg-info text-dark">Penghuni</span>', false);
    }
}
