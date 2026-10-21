<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class MidtransWebhookGrossAmountTest extends TestCase
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

    public function test_it_rejects_mismatched_gross_amount()
    {
        $this->midtransServiceMock->shouldReceive('isValidSignature')->once()->andReturn(true);

        $user = User::factory()->create();
        $tipe = \App\Models\TipeKamar::firstOrCreate(
            ['slug' => 'standar'],
            ['nama_tipe' => 'Standar', 'harga_bulanan' => 1000000]
        );
        $kamar = Kamar::create([
            'tipe_kamar_id' => $tipe->id,
            'nomor_kamar' => 'K-101',
            'lantai' => 1,
            'harga' => 1000000,
            'status' => 'Tersedia',
            'fasilitas' => 'AC',
            'deskripsi' => 'Deskripsi',
        ]);
        $booking = Booking::create([
            'kode_booking' => 'BKG-12345',
            'user_id' => $user->id,
            'kamar_id' => $kamar->id,
            'midtrans_order_id' => 'BKG-12345',
            'tipe_pembayaran' => 'Lunas',
            'total_harga' => 1000000,
            'status' => 'Menunggu Pembayaran',
            'tanggal_mulai' => now()->toDateString(),
            'durasi_bulan' => 1,
        ]);

        $payload = [
            'order_id' => $booking->midtrans_order_id,
            'status_code' => '200',
            'gross_amount' => '999000', // mismatched amount
            'signature_key' => 'valid',
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'transaction_id' => 'trx-xyz',
            'payment_type' => 'qris',
        ];

        $response = $this->postJson('/midtrans/webhook', $payload);
        $response->assertStatus(400);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Gross amount does not match expected payment amount',
        ]);
    }
}
