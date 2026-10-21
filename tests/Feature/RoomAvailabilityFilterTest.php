<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Kamar;
use App\Models\TipeKamar;
use App\Models\Booking;
use Carbon\Carbon;

class RoomAvailabilityFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a basic TipeKamar for rooms
        TipeKamar::create([
            'nama_tipe' => 'Standard',
            'slug' => 'standard',
            'harga_bulanan' => 2000000,
            'fasilitas_dasar' => 'AC, Kasur',
            'deskripsi' => 'Standard room',
        ]);
    }

    /** @test */
    public function it_shows_all_rooms_when_no_date_filter_is_applied()
    {
        $user = User::factory()->create();
        $kamar = Kamar::create([
            'tipe_kamar_id' => 1,
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

    /** @test */
    public function it_excludes_rooms_with_overlapping_bookings()
    {
        $user = User::factory()->create();
        $kamar1 = Kamar::create([
            'tipe_kamar_id' => 1,
            'nomor_kamar' => '102',
            'lantai' => 1,
            'harga' => 2000000,
            'fasilitas' => 'AC',
            'deskripsi' => 'Room with booking',
            'status' => 'Tersedia',
        ]);
        $kamar2 = Kamar::create([
            'tipe_kamar_id' => 1,
            'nomor_kamar' => '103',
            'lantai' => 1,
            'harga' => 2000000,
            'fasilitas' => 'AC',
            'deskripsi' => 'Free room',
            'status' => 'Tersedia',
        ]);

        // Booking that occupies Jan-Feb 2023 for kamar1
        Booking::create([
            'kode_booking' => 'BK001',
            'user_id' => $user->id,
            'kamar_id' => $kamar1->id,
            'tanggal_mulai' => Carbon::create(2023, 1, 1),
            'durasi_bulan' => 2,
            'tipe_pembayaran' => 'DP',
            'total_harga' => 4000000,
            'status' => 'Aktif',
        ]);

        // Filter period that overlaps the booking
        $response = $this->get(route('kamar.index', [
            'start_date' => '2023-01-15',
            'end_date' => '2023-01-31',
        ]));
        $response->assertOk();
        $response->assertViewHas('kamars', function ($kamars) use ($kamar2, $kamar1) {
            return $kamars->contains($kamar2) && ! $kamars->contains($kamar1);
        });
    }

    /** @test */
    public function it_includes_rooms_when_existing_booking_ends_before_requested_start()
    {
        $user = User::factory()->create();
        $kamar = Kamar::create([
            'tipe_kamar_id' => 1,
            'nomor_kamar' => '104',
            'lantai' => 1,
            'harga' => 2000000,
            'fasilitas' => 'AC',
            'deskripsi' => 'Room with early booking',
            'status' => 'Tersedia',
        ]);

        // Booking that ends before the filter start date (Jan 2023, durasi 1 month -> ends Jan 31)
        Booking::create([
            'kode_booking' => 'BK002',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => Carbon::create(2023, 1, 1),
            'durasi_bulan' => 1,
            'tipe_pembayaran' => 'DP',
            'total_harga' => 2000000,
            'status' => 'Aktif',
        ]);

        // Filter period after the booking ends
        $response = $this->get(route('kamar.index', [
            'start_date' => '2023-02-01',
            'end_date' => '2023-02-28',
        ]));
        $response->assertOk();
        $response->assertViewHas('kamars', function ($kamars) use ($kamar) {
            return $kamars->contains($kamar);
        });
    }
}
