<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    /** @var MockInterface */
    protected $midtransServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->midtransServiceMock = Mockery::mock(MidtransService::class);
        $this->app->instance(MidtransService::class, $this->midtransServiceMock);
    }

    protected function createKamar(string $status = 'Tersedia', float $harga = 1000000): Kamar
    {
        $tipe = \App\Models\TipeKamar::firstOrCreate(
            ['slug' => 'standar'],
            ['nama_tipe' => 'Standar', 'harga_bulanan' => $harga]
        );

        return Kamar::create([
            'tipe_kamar_id' => $tipe->id,
            'nomor_kamar' => 'K-'.uniqid(),
            'lantai' => 1,
            'harga' => $harga,
            'status' => $status,
            'fasilitas' => 'AC',
            'deskripsi' => 'Deskripsi',
        ]);
    }

    public function test_it_rejects_invalid_signature()
    {
        $this->midtransServiceMock->shouldReceive('isValidSignature')->once()->andReturn(false);
        $payload = [
            'order_id' => 'BKG-12345',
            'status_code' => '200',
            'gross_amount' => '500000',
            'signature_key' => 'invalid',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'trx-001',
            'payment_type' => 'qris',
        ];
        $response = $this->postJson('/midtrans/webhook', $payload);
        $response->assertStatus(401);
        $response->assertJson(['status' => 'error', 'message' => 'Invalid signature key']);
    }

    public function test_it_handles_successful_full_payment_and_activates_booking()
    {
        $this->midtransServiceMock->shouldReceive('isValidSignature')->once()->andReturn(true);
        $user = User::factory()->create();
        $kamar = $this->createKamar('Tersedia', 1000000);
        $booking = Booking::create([
            'kode_booking' => 'BKG-99999',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'midtrans_order_id' => 'BKG-99999',
            'tipe_pembayaran' => 'Lunas',
            'total_harga' => 1000000,
            'status' => 'Menunggu Pembayaran',
            'tanggal_mulai' => now()->toDateString(),
            'durasi_bulan' => 1,
        ]);
        $payload = [
            'order_id' => $booking->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => (string) $booking->total_harga,
            'signature_key' => 'valid',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'trx-abc123',
            'payment_type' => 'qris',
        ];
        $response = $this->postJson('/midtrans/webhook', $payload);
        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'Aktif',
            'midtrans_status' => 'settlement',
        ]);
        $this->assertDatabaseHas('kamar', [
            'id' => $kamar->id,
            'status' => 'Terisi',
        ]);
        $this->assertDatabaseHas('pembayarans', [
            'booking_id' => $booking->id,
            'status' => 'Lunas',
            'midtrans_transaction_id' => 'trx-abc123',
        ]);
        $this->assertDatabaseHas('sewas', [
            'booking_id' => $booking->id,
            'status' => 'Aktif',
        ]);
    }

    public function test_it_is_idempotent_for_duplicate_success_webhook()
    {
        $this->midtransServiceMock->shouldReceive('isValidSignature')->twice()->andReturn(true);
        $user = User::factory()->create();
        $kamar = $this->createKamar('Tersedia', 1500000);
        $booking = Booking::create([
            'kode_booking' => 'BKG-77777',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'midtrans_order_id' => 'BKG-77777',
            'tipe_pembayaran' => 'Lunas',
            'total_harga' => 1500000,
            'status' => 'Menunggu Pembayaran',
            'tanggal_mulai' => now()->toDateString(),
            'durasi_bulan' => 1,
        ]);
        $payload = [
            'order_id' => $booking->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => (string) $booking->total_harga,
            'signature_key' => 'valid',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'trx-dup',
            'payment_type' => 'qris',
        ];
        $this->postJson('/midtrans/webhook', $payload)->assertStatus(200);
        $this->postJson('/midtrans/webhook', $payload)->assertStatus(200);
        $this->assertEquals(1, Pembayaran::where('booking_id', $booking->id)->count());
    }

    public function test_active_booking_integrity_ensures_payment_sewa_and_room()
    {
        $user = User::factory()->create();
        $kamar = $this->createKamar('Sedang Diproses', 1200000);
        $booking = Booking::create([
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'kode_booking' => 'BKG-TEST-INTEGRITY',
            'midtrans_order_id' => 'QRIS-BKG-TEST-INTEGRITY',
            'tipe_pembayaran' => 'Lunas',
            'total_harga' => 1200000,
            'status' => 'Aktif',
            'midtrans_status' => 'settlement',
            'tanggal_mulai' => now()->toDateString(),
            'durasi_bulan' => 1,
        ]);

        // Prior to ensuring integrity: no payment, no sewa, room is not Terisi
        $this->assertNull($booking->sewa);
        $this->assertEquals(0, $booking->pembayarans()->count());
        $this->assertEquals('Sedang Diproses', $kamar->status);

        Booking::ensureActiveBookingIntegrity();

        $booking->refresh();
        $kamar->refresh();

        $this->assertNotNull($booking->sewa);
        $this->assertEquals('Aktif', $booking->sewa->status);
        $this->assertEquals('Terisi', $kamar->status);
        $this->assertEquals(1, $booking->pembayarans()->count());
        $this->assertEquals('Lunas', $booking->pembayarans()->first()->status);
    }
}
