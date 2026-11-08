<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\Keluhan;
use App\Models\Pembayaran;
use App\Models\Permission;
use App\Models\Rating;
use App\Models\Role;
use App\Models\Sewa;
use App\Models\TipeKamar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemilikKostLaporanTest extends TestCase
{
    use RefreshDatabase;

    private User $pemilikKost;

    private TipeKamar $tipeKamar;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(
            ['slug' => 'pemilik-kost'],
            ['name' => 'Pemilik Kost', 'description' => 'Pemilik Kost']
        );

        $permission = Permission::firstOrCreate(
            ['slug' => 'view-laporan-operasional'],
            ['name' => 'view-laporan-operasional', 'module' => 'Laporan', 'description' => 'Lihat Laporan']
        );

        $role->permissions()->sync([$permission->id]);

        $this->pemilikKost = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'Aktif',
        ]);

        $this->tipeKamar = TipeKamar::create([
            'nama_tipe' => 'Standar',
            'slug' => 'standar',
            'harga_bulanan' => 1000000,
            'fasilitas_dasar' => 'Kasur, Lemari',
            'deskripsi' => 'Kamar standar',
        ]);
    }

    public function test_default_report_uses_current_month_and_has_no_month_year_dropdown(): void
    {
        $response = $this->actingAs($this->pemilikKost)->get(route('pemilik-kost.laporan.index'));

        $response->assertOk();
        $response->assertViewIs('pemilik-kost.laporan.index');

        $now = Carbon::now();
        $expectedDari = $now->copy()->startOfMonth()->toDateString();
        $expectedSampai = $now->copy()->endOfMonth()->toDateString();

        $response->assertViewHas('tanggalDari', $expectedDari);
        $response->assertViewHas('tanggalSampai', $expectedSampai);

        // Pastikan dropdown bulan dan tahun serta tombol Tampilkan lama sudah dihapus
        $response->assertDontSee('name="bulan"', false);
        $response->assertDontSee('name="tahun"', false);
        $response->assertDontSee('>Tampilkan</button>', false);

        // Pastikan filter tanggal bayar dan fitur cetak tetap ada
        $response->assertSee('name="tanggal_dari"', false);
        $response->assertSee('name="tanggal_sampai"', false);
        $response->assertSee('Terapkan Periode');
        $response->assertSee('Cetak / Ekspor PDF');
    }

    public function test_report_filters_all_summaries_and_transactions_consistently(): void
    {
        $penghuni1 = User::factory()->create();
        $penghuni2 = User::factory()->create();

        $kamar1 = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '101',
            'lantai' => 1,
            'harga' => 1000000,
            'status' => 'Tersedia',
        ]);

        $kamar2 = Kamar::create([
            'tipe_kamar_id' => $this->tipeKamar->id,
            'nomor_kamar' => '102',
            'lantai' => 1,
            'harga' => 1000000,
            'status' => 'Tersedia',
        ]);

        // Range uji: 2026-05-01 s/d 2026-05-31
        $startDate = '2026-05-01';
        $endDate = '2026-05-31';

        // 1. Pembayaran Masuk (1 di dalam range, 1 di luar range)
        Pembayaran::create([
            'kode_pembayaran' => 'PAY-MEI-001',
            'user_id' => $penghuni1->id,
            'jenis_pembayaran' => 'Booking Awal',
            'nominal' => 1500000,
            'status' => 'Lunas',
            'tanggal_bayar' => '2026-05-15',
        ]);

        Pembayaran::create([
            'kode_pembayaran' => 'PAY-JUNI-002',
            'user_id' => $penghuni2->id,
            'jenis_pembayaran' => 'Booking Awal',
            'nominal' => 2000000,
            'status' => 'Lunas',
            'tanggal_bayar' => '2026-06-10',
        ]);

        // 2. Okupansi: Kamar 1 disewa di Mei 2026, Kamar 2 disewa di Juli 2026
        Sewa::create([
            'user_id' => $penghuni1->id,
            'kamar_id' => $kamar1->id,
            'tanggal_mulai' => '2026-05-01',
            'tanggal_selesai' => '2026-05-31',
            'status' => 'Selesai',
            'harga_per_bulan' => 1000000,
        ]);

        Sewa::create([
            'user_id' => $penghuni2->id,
            'kamar_id' => $kamar2->id,
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2026-07-31',
            'status' => 'Aktif',
            'harga_per_bulan' => 1000000,
        ]);

        // 3. Keluhan (1 selesai di Mei, 1 belum selesai di Mei, 1 di luar range)
        $keluhan1 = Keluhan::create([
            'kode_keluhan' => 'KLH-MEI-01',
            'user_id' => $penghuni1->id,
            'kamar_id' => $kamar1->id,
            'judul' => 'Kran Bocor',
            'isi' => 'Kran air kamar mandi bocor',
            'status' => 'Selesai',
        ]);
        $keluhan1->created_at = '2026-05-10 10:00:00';
        $keluhan1->save();

        $keluhan2 = Keluhan::create([
            'kode_keluhan' => 'KLH-MEI-02',
            'user_id' => $penghuni1->id,
            'kamar_id' => $kamar1->id,
            'judul' => 'Lampu Mati',
            'isi' => 'Lampu kamar mandi mati',
            'status' => 'Diproses',
        ]);
        $keluhan2->created_at = '2026-05-12 11:00:00';
        $keluhan2->save();

        $keluhan3 = Keluhan::create([
            'kode_keluhan' => 'KLH-APR-01',
            'user_id' => $penghuni2->id,
            'kamar_id' => $kamar2->id,
            'judul' => 'AC Rusak',
            'isi' => 'AC tidak dingin',
            'status' => 'Selesai',
        ]);
        $keluhan3->created_at = '2026-04-10 10:00:00';
        $keluhan3->save();

        // 4. Rating (1 di Mei skor 4, 1 di luar range skor 1)
        $rating1 = Rating::create([
            'user_id' => $penghuni1->id,
            'kamar_id' => $kamar1->id,
            'jenis_rating' => 'Kost',
            'skor' => 4,
        ]);
        $rating1->created_at = '2026-05-20 14:00:00';
        $rating1->save();

        $rating2 = Rating::create([
            'user_id' => $penghuni2->id,
            'kamar_id' => $kamar2->id,
            'jenis_rating' => 'Kost',
            'skor' => 1,
        ]);
        $rating2->created_at = '2026-06-01 09:00:00';
        $rating2->save();

        $response = $this->actingAs($this->pemilikKost)->get(route('pemilik-kost.laporan.index', [
            'tanggal_dari' => $startDate,
            'tanggal_sampai' => $endDate,
        ]));

        $response->assertOk();

        // 1. Penerimaan
        $response->assertViewHas('totalPendapatan', 1500000.00);
        $response->assertSee('PAY-MEI-001');
        $response->assertDontSee('PAY-JUNI-002');

        // 2. Okupansi: 1 kamar terisi dari 2 total kamar = 50%
        $response->assertViewHas('totalKamar', 2);
        $response->assertViewHas('kamarTerisi', 1);
        $response->assertViewHas('okupansiPersen', 50.0);

        // 3. Keluhan: 1 selesai dari 2 total keluhan
        $response->assertViewHas('keluhanSelesaiBulanIni', 1);
        $response->assertViewHas('totalKeluhanBulanIni', 2);

        // 4. Rating: Hanya rating Mei (skor 4)
        $response->assertViewHas('avgRatingKost', 4.0);
    }

    public function test_empty_or_inverted_date_parameters_are_handled_gracefully(): void
    {
        // Parameter kosong harus menggunakan bulan berjalan
        $response = $this->actingAs($this->pemilikKost)->get(route('pemilik-kost.laporan.index', [
            'tanggal_dari' => '',
            'tanggal_sampai' => '',
        ]));

        $response->assertOk();
        $now = Carbon::now();
        $response->assertViewHas('tanggalDari', $now->copy()->startOfMonth()->toDateString());
        $response->assertViewHas('tanggalSampai', $now->copy()->endOfMonth()->toDateString());

        // Parameter terbalik (start > end) harus ditukar dengan aman
        $responseReversed = $this->actingAs($this->pemilikKost)->get(route('pemilik-kost.laporan.index', [
            'tanggal_dari' => '2026-08-31',
            'tanggal_sampai' => '2026-08-01',
        ]));

        $responseReversed->assertOk();
        $responseReversed->assertViewHas('tanggalDari', '2026-08-01');
        $responseReversed->assertViewHas('tanggalSampai', '2026-08-31');
    }
}
