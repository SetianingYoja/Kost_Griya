<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengelolaUrlMigrationTest extends TestCase
{
    use RefreshDatabase;

    private User $pengelola;

    private User $pemilikKost;

    private User $superAdmin;

    private User $penghuni;

    protected function setUp(): void
    {
        parent::setUp();

        $pengelolaRole = Role::firstOrCreate(
            ['slug' => 'pengelola'],
            ['name' => 'Pengelola', 'description' => 'Pengelola operasional kost']
        );

        $pemilikKostRole = Role::firstOrCreate(
            ['slug' => 'pemilik-kost'],
            ['name' => 'Pemilik Kost', 'description' => 'Pemilik kost read-only']
        );

        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Super Administrator']
        );

        $penghuniRole = Role::firstOrCreate(
            ['slug' => 'penghuni'],
            ['name' => 'Penghuni', 'description' => 'Penghuni kost']
        );

        $permissions = [
            'view-kamar', 'manage-kamar', 'validate-booking', 'validate-pembayaran',
            'manage-tagihan', 'view-laporan-operasional', 'manage-keluhan', 'manage-rating',
        ];

        foreach ($permissions as $slug) {
            $perm = Permission::firstOrCreate(
                ['slug' => $slug],
                ['name' => $slug, 'module' => 'General', 'description' => $slug]
            );
            if (! $pengelolaRole->permissions->contains('id', $perm->id)) {
                $pengelolaRole->permissions()->attach($perm->id);
            }
        }

        $this->pengelola = User::factory()->create([
            'role_id' => $pengelolaRole->id,
            'status' => 'Aktif',
            'name' => 'Ibu Pengelola',
        ]);

        $this->pemilikKost = User::factory()->create([
            'role_id' => $pemilikKostRole->id,
            'status' => 'Aktif',
            'name' => 'Bapak Pemilik Kost',
        ]);

        $this->superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'status' => 'Aktif',
            'name' => 'Super Admin',
        ]);

        $this->penghuni = User::factory()->create([
            'role_id' => $penghuniRole->id,
            'status' => 'Aktif',
            'name' => 'Penghuni Satu',
        ]);
    }

    public function test_pengelola_routes_use_pengelola_url(): void
    {
        $url = route('pengelola.dashboard');
        $this->assertStringContainsString('/pengelola/dashboard', $url);

        $urlKamar = route('pengelola.kamar.index');
        $this->assertStringContainsString('/pengelola/kamar', $urlKamar);

        // Pastikan URL operasional tidak lagi menggunakan /pemilik/
        $this->assertStringNotContainsString('/pemilik/', $url);
        $this->assertStringNotContainsString('/pemilik/', $urlKamar);
    }

    public function test_pengelola_can_access_new_pengelola_dashboard_url(): void
    {
        $response = $this->actingAs($this->pengelola)->get('/pengelola/dashboard');
        $response->assertOk();
        $response->assertSee('Dashboard Pengelola');
    }

    public function test_backward_compatibility_redirects_old_pemilik_urls_to_pengelola(): void
    {
        // 1. /pemilik -> /pengelola/dashboard (301)
        $response1 = $this->actingAs($this->pengelola)->get('/pemilik');
        $response1->assertRedirect('/pengelola/dashboard');
        $this->assertSame(301, $response1->getStatusCode());

        // 2. /pemilik/dashboard -> /pengelola/dashboard (301)
        $response2 = $this->actingAs($this->pengelola)->get('/pemilik/dashboard');
        $response2->assertRedirect('/pengelola/dashboard');
        $this->assertSame(301, $response2->getStatusCode());

        // 3. /pemilik/kamar -> /pengelola/kamar (301)
        $response3 = $this->actingAs($this->pengelola)->get('/pemilik/kamar');
        $response3->assertRedirect('/pengelola/kamar');
        $this->assertSame(301, $response3->getStatusCode());
    }

    public function test_pemilik_kost_cannot_access_pengelola_routes(): void
    {
        $response = $this->actingAs($this->pemilikKost)->get('/pengelola/dashboard');
        $response->assertForbidden();

        $responseOld = $this->actingAs($this->pemilikKost)->get('/pemilik/dashboard');
        $responseOld->assertForbidden();

        // Pemilik kost tetap bisa akses dashboard read-only miliknya
        $responseOwner = $this->actingAs($this->pemilikKost)->get(route('pemilik-kost.dashboard'));
        $responseOwner->assertOk();
    }

    public function test_penghuni_cannot_access_pengelola_routes(): void
    {
        $response = $this->actingAs($this->penghuni)->get('/pengelola/dashboard');
        $response->assertForbidden();
    }

    public function test_super_admin_can_access_pengelola_routes(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/pengelola/dashboard');
        $response->assertOk();
    }

    public function test_auth_controller_redirects_pengelola_login_to_pengelola_dashboard(): void
    {
        $authController = app(AuthController::class);
        $redirect = $authController->redirectBasedOnRole($this->pengelola);

        $this->assertStringContainsString('/pengelola/dashboard', $redirect->getTargetUrl());
    }
}
