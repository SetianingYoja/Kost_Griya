<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sewa;
use App\Models\TipeKamar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPhaseOneTest extends TestCase
{
    use RefreshDatabase;

    private User $penghuni1;

    private User $penghuni2;

    private Kamar $kamar;

    private TipeKamar $tipe;

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

        $this->penghuni1 = User::factory()->create([
            'role_id' => $rolePenghuni->id,
            'status' => 'Aktif',
            'phone' => '081234567890',
        ]);

        $this->penghuni2 = User::factory()->create([
            'role_id' => $rolePenghuni->id,
            'status' => 'Aktif',
            'phone' => '081234567891',
        ]);

        $this->tipe = TipeKamar::create([
            'nama_tipe' => 'Standard Cozy',
            'slug' => 'standard-cozy',
            'harga_bulanan' => 1000000,
        ]);

        $this->kamar = Kamar::create([
            'tipe_kamar_id' => $this->tipe->id,
            'nomor_kamar' => 'Kamar 101',
            'lantai' => 1,
            'harga' => 1000000,
            'status' => 'Tersedia',
            'fasilitas' => 'AC, Kasur, Lemari',
            'deskripsi' => 'Kamar nyaman dan tenang',
        ]);
    }

    public function test_booking_directly_sets_status_menunggu_pembayaran_and_kamar_sedang_diproses(): void
    {
        $response = $this->actingAs($this->penghuni1)->post(route('penghuni.booking.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->toDateString(),
            'durasi_bulan' => 3,
            'tipe_pembayaran' => 'DP',
            'catatan' => 'Check in sore',
        ]);

        $booking = Booking::where('user_id', $this->penghuni1->id)->first();
        $this->assertNotNull($booking);

        $response->assertRedirect(route('penghuni.booking.bayar', $booking->id));

        // Booking langsung Menunggu Pembayaran tanpa butuh approval pemilik
        $this->assertSame('Menunggu Pembayaran', $booking->status);
        $this->assertSame('DP', $booking->tipe_pembayaran);
        $this->assertEquals(300000, (int) $booking->nominal_dp); // 30% dari 1.000.000
        $this->assertEquals(3000000, (int) $booking->total_harga);

        // Kamar otomatis menjadi Sedang Diproses
        $this->kamar->refresh();
        $this->assertSame('Sedang Diproses', $this->kamar->status);
    }

    public function test_booking_with_lunas_calculates_total_price_correctly(): void
    {
        $response = $this->actingAs($this->penghuni1)->post(route('penghuni.booking.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->toDateString(),
            'durasi_bulan' => 6,
            'tipe_pembayaran' => 'Lunas',
        ]);

        $booking = Booking::where('user_id', $this->penghuni1->id)->first();
        $this->assertNotNull($booking);

        $response->assertRedirect(route('penghuni.booking.bayar', $booking->id));
        $this->assertSame('Menunggu Pembayaran', $booking->status);
        $this->assertSame('Lunas', $booking->tipe_pembayaran);
        $this->assertEquals(0, (int) $booking->nominal_dp);
        $this->assertEquals(6000000, (int) $booking->total_harga);
    }

    public function test_other_user_cannot_book_room_that_is_sedang_diproses(): void
    {
        // Penghuni 1 booking kamar terlebih dahulu
        $this->actingAs($this->penghuni1)->post(route('penghuni.booking.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->toDateString(),
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'DP',
        ]);

        $this->kamar->refresh();
        $this->assertSame('Sedang Diproses', $this->kamar->status);

        // Penghuni 2 mencoba membooking kamar yang sama
        $response = $this->actingAs($this->penghuni2)->from(route('kamar.detail', $this->kamar->id))->post(route('penghuni.booking.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->toDateString(),
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'Lunas',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(1, Booking::count()); // Tidak ada booking kedua
    }

    public function test_public_views_display_sedang_diproses_status(): void
    {
        $this->kamar->status = 'Sedang Diproses';
        $this->kamar->save();

        // 1. Home page
        $responseHome = $this->get(route('home'));
        $responseHome->assertStatus(200);
        $responseHome->assertSee('Sedang Diproses');

        // 2. Kamar index page
        $responseIndex = $this->get(route('kamar.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Sedang Diproses');

        // 3. Kamar detail page
        $responseDetail = $this->get(route('kamar.detail', $this->kamar->id));
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee('Sedang Diproses Booking');
        $responseDetail->assertDontSee('Lanjut ke Pembayaran QRIS');
    }

    public function test_cancelling_booking_releases_room_to_tersedia_if_clean(): void
    {
        $this->actingAs($this->penghuni1)->post(route('penghuni.booking.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->toDateString(),
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'DP',
        ]);

        $booking = Booking::where('user_id', $this->penghuni1->id)->first();
        $this->kamar->refresh();
        $this->assertSame('Sedang Diproses', $this->kamar->status);

        // Penghuni membatalkan booking
        $response = $this->actingAs($this->penghuni1)->patch(route('penghuni.booking.cancel', $booking->id));
        $response->assertRedirect(route('penghuni.dashboard'));

        $booking->refresh();
        $this->assertSame('Dibatalkan', $booking->status);

        // Kamar harus kembali menjadi Tersedia
        $this->kamar->refresh();
        $this->assertSame('Tersedia', $this->kamar->status);
    }

    public function test_cancelling_booking_does_not_release_room_if_active_sewa_exists(): void
    {
        $this->actingAs($this->penghuni1)->post(route('penghuni.booking.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->toDateString(),
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'DP',
        ]);

        $booking = Booking::where('user_id', $this->penghuni1->id)->first();

        // Simulasikan ada sewa aktif lain pada kamar tersebut
        Sewa::create([
            'user_id' => $this->penghuni2->id,
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->subMonths(1),
            'tanggal_selesai' => Carbon::today()->addMonths(2),
            'status' => 'Aktif',
            'harga_per_bulan' => 1000000,
        ]);

        $this->actingAs($this->penghuni1)->patch(route('penghuni.booking.cancel', $booking->id));

        // Kamar tidak boleh menjadi Tersedia, melainkan harus Terisi
        $this->kamar->refresh();
        $this->assertSame('Terisi', $this->kamar->status);
    }

    public function test_cancelling_booking_does_not_release_room_if_valid_payment_exists(): void
    {
        $this->actingAs($this->penghuni1)->post(route('penghuni.booking.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->toDateString(),
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'DP',
        ]);

        $booking = Booking::where('user_id', $this->penghuni1->id)->first();

        // Buat pembayaran valid menunggu validasi
        Pembayaran::create([
            'kode_pembayaran' => 'PAY-TEST-001',
            'user_id' => $this->penghuni1->id,
            'booking_id' => $booking->id,
            'jenis_pembayaran' => 'Booking Awal',
            'nominal' => 300000,
            'metode_pembayaran' => 'QRIS',
            'status' => 'Menunggu Validasi',
            'tanggal_bayar' => Carbon::today()->toDateString(),
        ]);

        // Coba batalkan booking yang sudah memiliki pembayaran
        $response = $this->actingAs($this->penghuni1)->patch(route('penghuni.booking.cancel', $booking->id));
        $response->assertSessionHas('error');

        // Status booking tidak berubah dan kamar tetap Sedang Diproses
        $booking->refresh();
        $this->assertSame('Menunggu Pembayaran', $booking->status);

        $this->kamar->refresh();
        $this->assertSame('Sedang Diproses', $this->kamar->status);
    }

    public function test_expired_booking_releases_room_safely(): void
    {
        $this->actingAs($this->penghuni1)->post(route('penghuni.booking.store'), [
            'kamar_id' => $this->kamar->id,
            'tanggal_mulai' => Carbon::today()->toDateString(),
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'DP',
        ]);

        $booking = Booking::where('user_id', $this->penghuni1->id)->first();
        // Manipulasi batas pembayaran ke masa lalu
        $booking->batas_pembayaran = Carbon::now()->subMinutes(10);
        $booking->save();

        // Buka halaman bayar setelah kadaluarsa
        $response = $this->actingAs($this->penghuni1)->get(route('penghuni.booking.bayar', $booking->id));
        $response->assertRedirect(route('penghuni.booking.show', $booking->id));

        $booking->refresh();
        $this->assertSame('Kadaluarsa', $booking->status);

        $this->kamar->refresh();
        $this->assertSame('Tersedia', $this->kamar->status);
    }
}
