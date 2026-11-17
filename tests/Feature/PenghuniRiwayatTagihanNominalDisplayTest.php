<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tagihan;
use App\Models\TipeKamar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenghuniRiwayatTagihanNominalDisplayTest extends TestCase
{
    use RefreshDatabase;

    private User $pengelola;

    private User $pemilikKost;

    private User $penghuni;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $rolePengelola = Role::firstOrCreate(
            ['slug' => 'pengelola'],
            ['name' => 'Pengelola', 'description' => 'Pengelola operasional kost']
        );

        $rolePemilikKost = Role::firstOrCreate(
            ['slug' => 'pemilik-kost'],
            ['name' => 'Pemilik Kost', 'description' => 'Pemilik kost read-only']
        );

        $rolePenghuni = Role::firstOrCreate(
            ['slug' => 'penghuni'],
            ['name' => 'Penghuni', 'description' => 'Penghuni kost']
        );

        $permManagePenghuni = Permission::firstOrCreate(
            ['slug' => 'manage-penghuni'],
            ['name' => 'manage-penghuni', 'module' => 'Penghuni', 'description' => 'Manage Penghuni']
        );

        $permViewPenghuniReadonly = Permission::firstOrCreate(
            ['slug' => 'view-penghuni-readonly'],
            ['name' => 'view-penghuni-readonly', 'module' => 'Penghuni', 'description' => 'View Penghuni Readonly']
        );

        $rolePengelola->permissions()->syncWithoutDetaching([$permManagePenghuni->id]);
        $rolePemilikKost->permissions()->syncWithoutDetaching([$permViewPenghuniReadonly->id]);

        $this->pengelola = User::factory()->create([
            'role_id' => $rolePengelola->id,
            'status' => 'Aktif',
        ]);

        $this->pemilikKost = User::factory()->create([
            'role_id' => $rolePemilikKost->id,
            'status' => 'Aktif',
        ]);

        $this->penghuni = User::factory()->create([
            'role_id' => $rolePenghuni->id,
            'status' => 'Aktif',
            'name' => 'Siti Rahma',
            'phone' => '081234567890',
        ]);

        $tipeKamar = TipeKamar::create([
            'nama_tipe' => 'Deluxe Room',
            'slug' => 'deluxe-room',
            'harga_bulanan' => 1850000,
            'fasilitas_dasar' => 'AC, Kasur, Kamar Mandi Dalam',
            'deskripsi' => 'Kamar Deluxe',
        ]);

        $this->kamar = Kamar::create([
            'tipe_kamar_id' => $tipeKamar->id,
            'nomor_kamar' => 'Kamar 101',
            'status' => 'Terisi',
            'harga' => 1850000,
            'fasilitas' => 'AC, Kasur',
        ]);
    }

    public function test_pengelola_sees_actual_nominal_instead_of_zero_for_lunas_tagihan(): void
    {
        // Tagihan yang dilunasi via booking awal memiliki nominal Rp1.850.000 dan total_bayar Rp0
        Tagihan::create([
            'nomor_tagihan' => 'INV-20261117-001-1',
            'user_id' => $this->penghuni->id,
            'kamar_id' => $this->kamar->id,
            'periode' => 'Bulan Pertama (November 2026)',
            'bulan_ke' => 1,
            'nominal' => 1850000.00,
            'potongan_dp' => 0.00,
            'total_bayar' => 0.00,
            'tanggal_jatuh_tempo' => Carbon::now()->toDateString(),
            'status' => 'Lunas',
        ]);

        $response = $this->actingAs($this->pengelola)->get(route('pengelola.penghuni.show', $this->penghuni->id));

        $response->assertOk();
        $response->assertSee('Riwayat Tagihan & Pembayaran', false);
        $response->assertSee('Nominal Tagihan');
        $response->assertSee('Rp 1.850.000');
        $response->assertDontSee('<td>Rp 0</td>', false);
    }

    public function test_pemilik_kost_sees_actual_nominal_instead_of_zero_for_lunas_tagihan(): void
    {
        Tagihan::create([
            'nomor_tagihan' => 'INV-20261117-002-1',
            'user_id' => $this->penghuni->id,
            'kamar_id' => $this->kamar->id,
            'periode' => 'Bulan Pertama (November 2026)',
            'bulan_ke' => 1,
            'nominal' => 1850000.00,
            'potongan_dp' => 0.00,
            'total_bayar' => 0.00,
            'tanggal_jatuh_tempo' => Carbon::now()->toDateString(),
            'status' => 'Lunas',
        ]);

        $response = $this->actingAs($this->pemilikKost)->get(route('pemilik-kost.penghuni.show', $this->penghuni->id));

        $response->assertOk();
        $response->assertSee('Riwayat Tagihan & Pembayaran', false);
        $response->assertSee('Nominal Tagihan');
        $response->assertSee('Rp 1.850.000');
        $response->assertDontSee('<td>Rp 0</td>', false);
    }
}
