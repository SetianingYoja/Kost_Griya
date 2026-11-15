<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sewa;
use App\Models\TipeKamar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingAvailabilityConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private User $penghuni;

    private User $pengelola;

    private TipeKamar $tipe;

    private Kamar $kamar;

    protected function setUp(): void
    {
        parent::setUp();

        $rolePenghuni = Role::firstOrCreate(
            ['slug' => 'penghuni'],
            ['name' => 'Penghuni', 'description' => 'Penyewa kamar']
        );

        $permissionBooking = Permission::firstOrCreate(
            ['slug' => 'create-booking'],
            ['name' => 'Buat Booking', 'module' => 'Booking', 'description' => 'Melakukan pemesanan kamar']
        );

        if (! $rolePenghuni->permissions->contains('id', $permissionBooking->id)) {
            $rolePenghuni->permissions()->attach($permissionBooking->id);
        }

        $this->penghuni = User::factory()->create([
            'role_id' => $rolePenghuni->id,
            'status' => 'Aktif',
            'phone' => '081234567890',
        ]);

        $rolePengelola = Role::firstOrCreate(
            ['slug' => 'pengelola'],
            ['name' => 'Pengelola', 'description' => 'Pengelola operasional kost']
        );

        $permissionManageKamar = Permission::firstOrCreate(
            ['slug' => 'manage-kamar'],
            ['name' => 'Kelola Kamar', 'module' => 'Kamar', 'description' => 'Mengelola kamar']
        );

        if (! $rolePengelola->permissions->contains('id', $permissionManageKamar->id)) {
            $rolePengelola->permissions()->attach($permissionManageKamar->id);
        }

        $this->pengelola = User::factory()->create([
            'role_id' => $rolePengelola->id,
            'status' => 'Aktif',
            'phone' => '081298765432',
        ]);

        $this->tipe = TipeKamar::create([
            'nama_tipe' => 'Deluxe Standard',
            'slug' => 'deluxe-standard',
            'harga_bulanan' => 1200000,
        ]);

        $this->kamar = Kamar::create([
            'tipe_kamar_id' => $this->tipe->id,
            'nomor_kamar' => 'kamar 20',
            'lantai' => 1,
            'harga' => 1200000,
            'status' => 'Tersedia',
            'fasilitas' => 'AC, TV, Kamar Mandi Dalam',
            'deskripsi' => 'Kamar luas dan bersih',
        ]);
    }

    public function test_kamar_display_name_formats_correctly(): void
    {
        $this->assertSame('Kamar 20', $this->kamar->display_name);

        $kamar2 = new Kamar(['nomor_kamar' => '105']);
        $this->assertSame('Kamar 105', $kamar2->display_name);

        $kamar3 = new Kamar(['nomor_kamar' => 'Kamar A1']);
        $this->assertSame('Kamar A1', $kamar3->display_name);
    }

    public function test_kamar_effective_status_reflects_active_sewa_even_when_status_is_tersedia(): void
    {
        // Kondisi data anomali: status kamar 'Tersedia', tapi ada sewa aktif
        $this->assertSame('Tersedia', $this->kamar->status);

        $penyewaLain = User::factory()->create();
        Sewa::create([
            'user_id' => $penyewaLain->id,
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->subDays(5),
            'tanggal_selesai' => Carbon::today()->addDays(25),
            'status' => 'Aktif',
            'harga_per_bulan' => 1200000,
        ]);

        $this->assertFalse($this->kamar->isAvailableForBooking());
        $this->assertSame('Terisi', $this->kamar->effective_status);
    }

    public function test_kamar_detail_page_shows_terisi_and_hides_booking_form_when_active_sewa_exists(): void
    {
        $penyewaLain = User::factory()->create();
        Sewa::create([
            'user_id' => $penyewaLain->id,
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->subDays(5),
            'tanggal_selesai' => Carbon::today()->addDays(25),
            'status' => 'Aktif',
            'harga_per_bulan' => 1200000,
        ]);

        $response = $this->get(route('kamar.detail', $this->kamar->id));
        $response->assertOk();
        $response->assertSee('Sedang Terisi');
        $response->assertSee('Kamar ini sedang <strong>Terisi</strong> dan tidak dapat dibooking saat ini.', false);
        $response->assertDontSee('Lanjut ke Pembayaran QRIS');
    }

    public function test_booking_attempt_on_room_with_active_sewa_returns_descriptive_error_without_double_kamar(): void
    {
        $penyewaLain = User::factory()->create();
        Sewa::create([
            'user_id' => $penyewaLain->id,
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->subDays(5),
            'tanggal_selesai' => Carbon::today()->addDays(25),
            'status' => 'Aktif',
            'harga_per_bulan' => 1200000,
        ]);

        $response = $this->actingAs($this->penghuni)->post(route('penghuni.booking.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->toDateString(),
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'DP',
        ]);

        $response->assertSessionHas('error');
        $errorMessage = session('error');
        $this->assertSame('Kamar 20 saat ini sedang disewa aktif dan tidak dapat dibooking.', $errorMessage);
        $this->assertStringNotContainsString('Kamar kamar 20', $errorMessage);
        $this->assertSame(0, Booking::count());
    }

    public function test_pemilik_cannot_update_room_status_to_tersedia_if_active_sewa_exists(): void
    {
        $penyewaLain = User::factory()->create();
        Sewa::create([
            'user_id' => $penyewaLain->id,
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->subDays(5),
            'tanggal_selesai' => Carbon::today()->addDays(25),
            'status' => 'Aktif',
            'harga_per_bulan' => 1200000,
        ]);

        // Kamar berstatus Terisi
        $this->kamar->status = 'Terisi';
        $this->kamar->save();

        // Pengelola/pemilik mencoba memaksa status kembali menjadi Tersedia
        $response = $this->actingAs($this->pengelola)->put(route('pemilik.kamar.update', $this->kamar->id), [
            'nomor_kamar' => $this->kamar->nomor_kamar,
            'tipe_kamar_id' => $this->tipe->id,
            'lantai' => 1,
            'harga' => 1200000,
            'status' => 'Tersedia',
            'fasilitas' => $this->kamar->fasilitas,
            'deskripsi' => $this->kamar->deskripsi,
        ]);

        $response->assertSessionHas('error', 'Status kamar tidak dapat diubah menjadi Tersedia karena kamar masih memiliki sewa aktif.');

        $this->kamar->refresh();
        $this->assertSame('Terisi', $this->kamar->status);
    }

    public function test_pemilik_can_update_other_room_fields_when_active_sewa_exists(): void
    {
        $penyewaLain = User::factory()->create();
        Sewa::create([
            'user_id' => $penyewaLain->id,
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->subDays(5),
            'tanggal_selesai' => Carbon::today()->addDays(25),
            'status' => 'Aktif',
            'harga_per_bulan' => 1200000,
        ]);

        $this->kamar->status = 'Terisi';
        $this->kamar->save();

        $response = $this->actingAs($this->pengelola)->put(route('pemilik.kamar.update', $this->kamar->id), [
            'nomor_kamar' => $this->kamar->nomor_kamar,
            'tipe_kamar_id' => $this->tipe->id,
            'lantai' => 1,
            'harga' => 1350000,
            'status' => 'Terisi',
            'fasilitas' => 'AC, TV, Water Heater',
            'deskripsi' => 'Deskripsi baru yang diperbarui',
        ]);

        $response->assertRedirect(route('pemilik.kamar.index'));
        $response->assertSessionHas('success');

        $this->kamar->refresh();
        $this->assertEquals(1350000, (int) $this->kamar->harga);
        $this->assertSame('AC, TV, Water Heater', $this->kamar->fasilitas);
        $this->assertSame('Terisi', $this->kamar->status);
    }
}

