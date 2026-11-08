<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Sewa;
use App\Models\TipeKamar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomAvailabilityFilterTest extends TestCase
{
    use RefreshDatabase;

    private TipeKamar $tipeKamar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tipeKamar = TipeKamar::create([
            'nama_tipe' => 'Standard',
            'slug' => 'standard',
            'harga_bulanan' => 2000000,
            'fasilitas_dasar' => 'AC, Kasur',
            'deskripsi' => 'Standard room',
        ]);
    }

    public function test_shows_all_rooms_when_no_date_filter_is_applied(): void
    {
        $kamar = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '101',
            'lantai' => 1,
            'harga' => 2000000,
            'fasilitas' => 'AC',
            'deskripsi' => 'Nice room',
            'status' => 'Tersedia',
        ]);

        $response = $this->get(route('kamar.index'));
        $response->assertOk();
        $response->assertViewHas('kamars', function ($kamars) use ($kamar) {
            return $kamars->contains($kamar);
        });
    }

    public function test_excludes_rooms_with_overlapping_bookings(): void
    {
        $user = User::factory()->create();
        $kamar1 = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '102',
            'lantai' => 1,
            'harga' => 2000000,
            'fasilitas' => 'AC',
            'deskripsi' => 'Room with booking',
            'status' => 'Tersedia',
        ]);
        $kamar2 = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '103',
            'lantai' => 1,
            'harga' => 2000000,
            'fasilitas' => 'AC',
            'deskripsi' => 'Free room',
            'status' => 'Tersedia',
        ]);

        // Booking that occupies Jan-Feb 2026 for kamar1
        Booking::create([
            'kode_booking' => 'BK001',
            'user_id' => $user->id,
            'kamar_id' => $kamar1->id,
            'tanggal_mulai' => Carbon::create(2026, 1, 1),
            'durasi_bulan' => 2,
            'tipe_pembayaran' => 'DP',
            'total_harga' => 4000000,
            'status' => 'Aktif',
        ]);

        // Filter period that overlaps the booking
        $response = $this->get(route('kamar.index', [
            'start_date' => '2026-01-15',
            'end_date' => '2026-01-31',
        ]));
        $response->assertOk();
        $response->assertViewHas('kamars', function ($kamars) use ($kamar2, $kamar1) {
            return $kamars->contains($kamar2) && ! $kamars->contains($kamar1);
        });
    }

    public function test_includes_rooms_when_existing_booking_ends_before_requested_start(): void
    {
        $user = User::factory()->create();
        $kamar = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '104',
            'lantai' => 1,
            'harga' => 2000000,
            'fasilitas' => 'AC',
            'deskripsi' => 'Room with early booking',
            'status' => 'Tersedia',
        ]);

        Booking::create([
            'kode_booking' => 'BK002',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => Carbon::create(2026, 1, 1),
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'DP',
            'total_harga' => 2000000,
            'status' => 'Aktif',
        ]);

        // Filter period after the booking ends (Feb 2026)
        $response = $this->get(route('kamar.index', [
            'start_date' => '2026-02-05',
            'end_date' => '2026-02-28',
        ]));
        $response->assertOk();
        $response->assertViewHas('kamars', function ($kamars) use ($kamar) {
            return $kamars->contains($kamar);
        });
    }

    public function test_includes_room_available_in_future_even_if_current_db_status_is_terisi(): void
    {
        $user = User::factory()->create();

        // Kamar currently marked 'Terisi' in DB with lease ending Oct 15
        $kamar = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '105',
            'lantai' => 1,
            'harga' => 2000000,
            'status' => 'Terisi',
        ]);

        Sewa::create([
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => '2026-09-15',
            'tanggal_selesai' => '2026-10-15',
            'status' => 'Aktif',
            'harga_per_bulan' => 2000000,
        ]);

        // Visitor searches for next month (Nov 1 - Nov 30) with status dropdown set to 'Tersedia'
        $response = $this->get(route('kamar.index', [
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'status' => 'Tersedia',
        ]));

        $response->assertOk();
        $response->assertViewHas('kamars', function ($kamars) use ($kamar) {
            return $kamars->contains($kamar);
        });
    }

    public function test_excludes_room_with_overlapping_active_sewa(): void
    {
        $user = User::factory()->create();

        $kamar = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '106',
            'lantai' => 1,
            'harga' => 2000000,
            'status' => 'Terisi',
        ]);

        Sewa::create([
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => '2026-11-01',
            'tanggal_selesai' => '2026-12-31',
            'status' => 'Aktif',
            'harga_per_bulan' => 2000000,
        ]);

        // Visitor searches overlapping period (Dec 1 - Dec 15)
        $response = $this->get(route('kamar.index', [
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-15',
        ]));

        $response->assertOk();
        $response->assertViewHas('kamars', function ($kamars) use ($kamar) {
            return ! $kamars->contains($kamar);
        });
    }

    public function test_expired_menunggu_pembayaran_booking_does_not_block_room(): void
    {
        $user = User::factory()->create();

        $kamar = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '107',
            'lantai' => 1,
            'harga' => 2000000,
            'status' => 'Tersedia',
        ]);

        // Booking with status Menunggu Pembayaran, but batas_pembayaran was yesterday
        Booking::create([
            'kode_booking' => 'BK-EXP-01',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => Carbon::now()->addDays(2),
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'DP',
            'total_harga' => 2000000,
            'status' => 'Menunggu Pembayaran',
            'batas_pembayaran' => Carbon::now()->subDay(),
        ]);

        $response = $this->get(route('kamar.index', [
            'start_date' => Carbon::now()->addDays(2)->toDateString(),
            'end_date' => Carbon::now()->addDays(25)->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewHas('kamars', function ($kamars) use ($kamar) {
            return $kamars->contains($kamar);
        });
    }

    public function test_active_menunggu_pembayaran_booking_blocks_room(): void
    {
        $user = User::factory()->create();

        $kamar = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '108',
            'lantai' => 1,
            'harga' => 2000000,
            'status' => 'Sedang Diproses',
        ]);

        // Booking with status Menunggu Pembayaran and batas_pembayaran is in future
        Booking::create([
            'kode_booking' => 'BK-ACT-01',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => Carbon::now()->addDays(2),
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'DP',
            'total_harga' => 2000000,
            'status' => 'Menunggu Pembayaran',
            'batas_pembayaran' => Carbon::now()->addHours(2),
        ]);

        $response = $this->get(route('kamar.index', [
            'start_date' => Carbon::now()->addDays(2)->toDateString(),
            'end_date' => Carbon::now()->addDays(25)->toDateString(),
        ]));

        $response->assertOk();
        $response->assertViewHas('kamars', function ($kamars) use ($kamar) {
            return ! $kamars->contains($kamar);
        });
    }

    public function test_does_not_double_block_when_booking_has_associated_sewa_that_ended(): void
    {
        $user = User::factory()->create();

        $kamar = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '109',
            'lantai' => 1,
            'harga' => 2000000,
            'status' => 'Terisi',
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BK-SEWA-01',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => '2026-09-01',
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'Lunas',
            'total_harga' => 2000000,
            'status' => 'Aktif',
        ]);

        // Associated sewa ended on 2026-09-30
        Sewa::create([
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'booking_id' => $booking->id,
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-30',
            'status' => 'Selesai',
            'harga_per_bulan' => 2000000,
        ]);

        // Filter for November 2026
        $response = $this->get(route('kamar.index', [
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
        ]));

        $response->assertOk();
        $response->assertViewHas('kamars', function ($kamars) use ($kamar) {
            return $kamars->contains($kamar);
        });
    }

    public function test_excludes_room_with_status_tidak_tersedia_even_when_date_filter_is_active(): void
    {
        $kamar = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '110',
            'lantai' => 1,
            'harga' => 2000000,
            'status' => 'Tidak tersedia',
            'deskripsi' => 'Sedang renovasi',
        ]);

        $response = $this->get(route('kamar.index', [
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
        ]));

        $response->assertOk();
        $response->assertViewHas('kamars', function ($kamars) use ($kamar) {
            return ! $kamars->contains($kamar);
        });
    }

    public function test_displays_tersedia_badge_when_date_filter_is_applied(): void
    {
        $kamar = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '111',
            'lantai' => 1,
            'harga' => 2000000,
            'status' => 'Terisi', // In DB it is marked Terisi today
        ]);

        $response = $this->get(route('kamar.index', [
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
        ]));

        $response->assertOk();
        $response->assertSee('status-tersedia');
        $response->assertDontSee('status-terisi');
    }
}
