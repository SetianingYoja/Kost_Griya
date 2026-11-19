<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\KamarFoto;
use App\Models\Keluhan;
use App\Models\Pembayaran;
use App\Models\Permission;
use App\Models\Perpanjangan;
use App\Models\Rating;
use App\Models\RiwayatAktivitas;
use App\Models\Role;
use App\Models\Sewa;
use App\Models\Tagihan;
use App\Models\TipeKamar;
use App\Models\User;
use App\Services\KamarCleanupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KamarCascadeDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private Role $rolePenghuni;

    private TipeKamar $tipeKamar;

    protected function setUp(): void
    {
        parent::setUp();

        $roleSuperAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Administrator Utama']
        );

        $rolePengelola = Role::firstOrCreate(
            ['slug' => 'pengelola'],
            ['name' => 'Pengelola', 'description' => 'Pengelola Kost']
        );

        $this->rolePenghuni = Role::firstOrCreate(
            ['slug' => 'penghuni'],
            ['name' => 'Penghuni', 'description' => 'Penyewa']
        );

        $perm = Permission::firstOrCreate(
            ['slug' => 'manage-kamar'],
            ['name' => 'manage-kamar', 'module' => 'Kamar', 'description' => 'Manage kamar']
        );
        $roleSuperAdmin->permissions()->syncWithoutDetaching([$perm->id]);
        $rolePengelola->permissions()->syncWithoutDetaching([$perm->id]);

        $this->superAdmin = User::factory()->create([
            'role_id' => $roleSuperAdmin->id,
            'name' => 'Super Admin Test',
            'status' => 'Aktif',
        ]);

        $this->tipeKamar = TipeKamar::create([
            'nama_tipe' => 'Tipe Standar Test',
            'slug' => 'standar-test',
            'harga_bulanan' => 1000000,
        ]);
    }

    private function createKamar(string $nomor = 'Kamar 101'): Kamar
    {
        return Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => $nomor,
            'lantai' => 1,
            'harga' => 1000000,
            'status' => 'Tersedia',
        ]);
    }

    public function test_kamar_tanpa_relasi_berhasil_dihapus(): void
    {
        $kamar = $this->createKamar('Kamar 101');

        $response = $this->actingAs($this->superAdmin)->delete(route('pengelola.kamar.destroy', $kamar->id));

        $response->assertRedirect(route('pengelola.kamar.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('kamar', ['id' => $kamar->id]);
        $this->assertDatabaseHas('riwayat_aktivitas', [
            'user_id' => $this->superAdmin->id,
            'judul' => 'Pembersihan Data Kamar',
        ]);
    }

    public function test_preview_endpoint_returns_accurate_metrics(): void
    {
        $kamar = $this->createKamar('Kamar 102');
        $user = User::factory()->create(['role_id' => $this->rolePenghuni->id]);

        Booking::create([
            'kode_booking' => 'BKG-001',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => now(),
            'durasi_bulan' => 1,
            'total_harga' => 1000000,
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($this->superAdmin)->getJson(route('pengelola.kamar.preview-destroy', $kamar->id));

        $response->assertStatus(200);
        $response->assertJson([
            'kamar' => [
                'nomor_kamar' => 'Kamar 102',
            ],
            'counts' => [
                'bookings' => 1,
                'sewas' => 0,
                'users_to_delete' => 1,
                'users_to_preserve' => 0,
            ],
        ]);
    }

    public function test_kamar_dengan_booking_berhasil_dihapus_beserta_booking_terkait(): void
    {
        $kamar = $this->createKamar('Kamar 103');
        $user = User::factory()->create(['role_id' => $this->rolePenghuni->id]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-103',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => now(),
            'durasi_bulan' => 1,
            'total_harga' => 1000000,
            'status' => 'Menunggu Pembayaran',
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('pengelola.kamar.destroy', $kamar->id));
        $response->assertRedirect(route('pengelola.kamar.index'));

        $this->assertDatabaseMissing('kamar', ['id' => $kamar->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }

    public function test_kamar_dengan_sewa_tagihan_pembayaran_dan_perpanjangan_berhasil_dibersihkan(): void
    {
        $kamar = $this->createKamar('Kamar 104');
        $user = User::factory()->create(['role_id' => $this->rolePenghuni->id]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-104',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => now(),
            'durasi_bulan' => 1,
            'total_harga' => 1000000,
            'status' => 'Aktif',
        ]);

        $sewa = Sewa::create([
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'booking_id' => $booking->id,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
            'status' => 'Aktif',
            'harga_per_bulan' => 1000000,
        ]);

        $perpanjangan = Perpanjangan::create([
            'sewa_id' => $sewa->id,
            'user_id' => $user->id,
            'durasi_bulan' => 1,
            'tanggal_mulai_baru' => now()->addMonth(),
            'tanggal_selesai_baru' => now()->addMonths(2),
            'nominal_total' => 1000000,
            'status' => 'Disetujui',
        ]);

        $tagihan = Tagihan::create([
            'nomor_tagihan' => 'INV-104',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'sewa_id' => $sewa->id,
            'perpanjangan_id' => $perpanjangan->id,
            'periode' => 'November 2026',
            'bulan_ke' => 1,
            'nominal' => 1000000,
            'potongan_dp' => 0,
            'total_bayar' => 1000000,
            'tanggal_jatuh_tempo' => now(),
            'status' => 'Lunas',
        ]);

        $pembayaranBooking = Pembayaran::create([
            'kode_pembayaran' => 'PAY-104-BKG',
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'jenis_pembayaran' => 'Booking Awal',
            'nominal' => 1000000,
            'status' => 'Lunas',
        ]);

        $pembayaranTagihan = Pembayaran::create([
            'kode_pembayaran' => 'PAY-104-TAG',
            'user_id' => $user->id,
            'tagihan_id' => $tagihan->id,
            'jenis_pembayaran' => 'Tagihan Bulanan',
            'nominal' => 1000000,
            'status' => 'Lunas',
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('pengelola.kamar.destroy', $kamar->id));
        $response->assertRedirect(route('pengelola.kamar.index'));

        // Pastikan seluruh entitas terkait terhapus tanpa ada error foreign key
        $this->assertDatabaseMissing('kamar', ['id' => $kamar->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
        $this->assertDatabaseMissing('sewas', ['id' => $sewa->id]);
        $this->assertDatabaseMissing('perpanjangans', ['id' => $perpanjangan->id]);
        $this->assertDatabaseMissing('tagihans', ['id' => $tagihan->id]);
        $this->assertDatabaseMissing('pembayarans', ['id' => $pembayaranBooking->id]);
        $this->assertDatabaseMissing('pembayarans', ['id' => $pembayaranTagihan->id]);
    }

    public function test_keluhan_dan_rating_terkait_kamar_ikut_dibersihkan(): void
    {
        $kamar = $this->createKamar('Kamar 105');
        $user = User::factory()->create(['role_id' => $this->rolePenghuni->id]);

        $keluhan = Keluhan::create([
            'kode_keluhan' => 'KLH-105',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'judul' => 'AC Kurang Dingin',
            'isi' => 'Mohon dicek freon AC',
            'status' => 'Menunggu',
        ]);

        $rating = Rating::create([
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'keluhan_id' => $keluhan->id,
            'jenis_rating' => 'Kost',
            'skor' => 5,
            'komentar' => 'Bagus',
            'status' => 'Disetujui',
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('pengelola.kamar.destroy', $kamar->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('kamar', ['id' => $kamar->id]);
        $this->assertDatabaseMissing('keluhans', ['id' => $keluhan->id]);
        $this->assertDatabaseMissing('ratings', ['id' => $rating->id]);
    }

    public function test_foto_kamar_dan_file_storage_terkait_dibersihkan_setelah_commit(): void
    {
        Storage::fake('public');

        $kamar = $this->createKamar('Kamar 106');
        $coverPath = 'kamar/cover_test_106.jpg';
        $galleryPath = 'kamar/gallery_test_106.jpg';

        Storage::disk('public')->put($coverPath, 'dummy cover');
        Storage::disk('public')->put($galleryPath, 'dummy gallery');

        $kamar->foto = $coverPath;
        $kamar->save();

        $foto = KamarFoto::create([
            'kamar_id' => $kamar->id,
            'foto' => $galleryPath,
            'is_cover' => false,
            'urutan' => 1,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($coverPath));
        $this->assertTrue(Storage::disk('public')->exists($galleryPath));

        $response = $this->actingAs($this->superAdmin)->delete(route('pengelola.kamar.destroy', $kamar->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('kamar', ['id' => $kamar->id]);
        $this->assertDatabaseMissing('kamar_fotos', ['id' => $foto->id]);

        $this->assertFalse(Storage::disk('public')->exists($coverPath));
        $this->assertFalse(Storage::disk('public')->exists($galleryPath));
    }

    public function test_akun_penghuni_yang_hanya_terkait_kamar_tersebut_dihapus_sesuai_aturan(): void
    {
        $kamar = $this->createKamar('Kamar 107');
        $userMurni = User::factory()->create([
            'role_id' => $this->rolePenghuni->id,
            'name' => 'Penghuni Murni 107',
        ]);

        Booking::create([
            'kode_booking' => 'BKG-107',
            'user_id' => $userMurni->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => now(),
            'durasi_bulan' => 1,
            'total_harga' => 1000000,
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('pengelola.kamar.destroy', $kamar->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('kamar', ['id' => $kamar->id]);
        $this->assertDatabaseMissing('users', ['id' => $userMurni->id]);
    }

    public function test_akun_penghuni_yang_memiliki_data_kamar_lain_tetap_dipertahankan(): void
    {
        $kamarA = $this->createKamar('Kamar 108A');
        $kamarB = $this->createKamar('Kamar 108B');

        $userMulti = User::factory()->create([
            'role_id' => $this->rolePenghuni->id,
            'name' => 'Penghuni Multi Kamar',
        ]);

        // Booking pada Kamar A
        Booking::create([
            'kode_booking' => 'BKG-108A',
            'user_id' => $userMulti->id,
            'kamar_id' => $kamarA->id,
            'tanggal_mulai' => now(),
            'durasi_bulan' => 1,
            'total_harga' => 1000000,
            'status' => 'Selesai',
        ]);

        // Sewa aktif pada Kamar B
        Sewa::create([
            'user_id' => $userMulti->id,
            'kamar_id' => $kamarB->id,
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addMonth(),
            'status' => 'Aktif',
            'harga_per_bulan' => 1000000,
        ]);

        // Hapus Kamar A
        $response = $this->actingAs($this->superAdmin)->delete(route('pengelola.kamar.destroy', $kamarA->id));
        $response->assertRedirect();

        // Kamar A terhapus, tetapi userMulti dan Kamar B tetap utuh
        $this->assertDatabaseMissing('kamar', ['id' => $kamarA->id]);
        $this->assertDatabaseHas('kamar', ['id' => $kamarB->id]);
        $this->assertDatabaseHas('users', ['id' => $userMulti->id]);
    }

    public function test_akun_super_admin_pengelola_dan_pemilik_kost_tidak_pernah_ikut_terhapus(): void
    {
        $rolePengelola = Role::where('slug', 'pengelola')->first();
        $pengelolaUser = User::factory()->create([
            'role_id' => $rolePengelola->id,
            'name' => 'Staff Pengelola',
        ]);

        $kamar = $this->createKamar('Kamar 109');

        // Pengelola membuat keluhan atau aktivitas di kamar ini
        Keluhan::create([
            'kode_keluhan' => 'KLH-109',
            'user_id' => $pengelolaUser->id,
            'kamar_id' => $kamar->id,
            'judul' => 'Pengecekan Rutin',
            'isi' => 'Kamar rapi',
            'status' => 'Selesai',
        ]);

        $response = $this->actingAs($this->superAdmin)->delete(route('pengelola.kamar.destroy', $kamar->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('kamar', ['id' => $kamar->id]);
        $this->assertDatabaseHas('users', ['id' => $pengelolaUser->id]);
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);
    }

    public function test_riwayat_aktivitas_penghapusan_tetap_tersimpan(): void
    {
        $kamar = $this->createKamar('Kamar 110');

        $this->actingAs($this->superAdmin)->delete(route('pengelola.kamar.destroy', $kamar->id));

        $riwayat = RiwayatAktivitas::where('user_id', $this->superAdmin->id)
            ->where('judul', 'Pembersihan Data Kamar')
            ->first();

        $this->assertNotNull($riwayat);
        $this->assertStringContainsString('Kamar 110', $riwayat->deskripsi);
        $this->assertStringContainsString($this->superAdmin->name, $riwayat->deskripsi);
    }

    public function test_transaksi_rollback_jika_terjadi_kegagalan_dan_tidak_meninggalkan_orphan(): void
    {
        $kamar = $this->createKamar('Kamar 111');
        $user = User::factory()->create(['role_id' => $this->rolePenghuni->id]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-111',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => now(),
            'durasi_bulan' => 1,
            'total_harga' => 1000000,
            'status' => 'Aktif',
        ]);

        // Mock cleanup service untuk memicu exception di tengah transaksi
        $mockService = $this->partialMock(KamarCleanupService::class, function ($mock) {
            $mock->shouldReceive('destroyWithRelated')
                ->andThrow(new \RuntimeException('Simulasi kegagalan database'));
        });

        try {
            $this->actingAs($this->superAdmin)->delete(route('pengelola.kamar.destroy', $kamar->id));
        } catch (\RuntimeException $e) {
            $this->assertSame('Simulasi kegagalan database', $e->getMessage());
        }

        // Pastikan tidak ada data yang terhapus sebagian karena rollback
        $this->assertDatabaseHas('kamar', ['id' => $kamar->id]);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_penghapusan_kamar_tidak_memengaruhi_kamar_lain(): void
    {
        $kamarA = $this->createKamar('Kamar 201A');
        $kamarB = $this->createKamar('Kamar 202B');

        $userB = User::factory()->create(['role_id' => $this->rolePenghuni->id]);
        $bookingB = Booking::create([
            'kode_booking' => 'BKG-202B',
            'user_id' => $userB->id,
            'kamar_id' => $kamarB->id,
            'tanggal_mulai' => now(),
            'durasi_bulan' => 1,
            'total_harga' => 1000000,
            'status' => 'Aktif',
        ]);

        $this->actingAs($this->superAdmin)->delete(route('pengelola.kamar.destroy', $kamarA->id));

        $this->assertDatabaseMissing('kamar', ['id' => $kamarA->id]);
        $this->assertDatabaseHas('kamar', ['id' => $kamarB->id]);
        $this->assertDatabaseHas('bookings', ['id' => $bookingB->id]);
        $this->assertDatabaseHas('users', ['id' => $userB->id]);
    }
}
