<?php

namespace Tests\Feature;

use App\Models\Rating;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatingModerasiTest extends TestCase
{
    use RefreshDatabase;

    private User $pemilik;

    private User $penghuni;

    protected function setUp(): void
    {
        parent::setUp();

        $rolePemilik = Role::firstOrCreate(
            ['slug' => 'pemilik-kost'],
            ['name' => 'Pemilik Kost', 'description' => 'Owner']
        );

        $rolePenghuni = Role::firstOrCreate(
            ['slug' => 'penghuni'],
            ['name' => 'Penghuni', 'description' => 'Tenant']
        );

        $this->pemilik = User::factory()->create([
            'role_id' => $rolePemilik->id,
            'status' => 'Aktif',
        ]);

        $this->penghuni = User::factory()->create([
            'role_id' => $rolePenghuni->id,
            'status' => 'Aktif',
        ]);
    }

    public function test_pemilik_can_access_rating_index(): void
    {
        $response = $this->actingAs($this->pemilik)->get(route('pemilik.rating.index'));
        $response->assertStatus(200);
        $response->assertSee('Rating & Ulasan Kepuasan Penghuni');
        $response->assertSee('Menunggu Validasi');
    }

    public function test_pemilik_can_approve_rating(): void
    {
        $rating = Rating::create([
            'user_id' => $this->penghuni->id,
            'jenis_rating' => 'Kost',
            'skor' => 5,
            'komentar' => 'Kost bersih dan nyaman',
            'status' => 'Menunggu Validasi',
        ]);

        $response = $this->actingAs($this->pemilik)->patch(route('pemilik.rating.approve', $rating->id));
        $response->assertRedirect();

        $rating->refresh();
        $this->assertSame('Disetujui', $rating->status);
        $this->assertNotNull($rating->disetujui_pada);
    }

    public function test_pemilik_can_reject_rating(): void
    {
        $rating = Rating::create([
            'user_id' => $this->penghuni->id,
            'jenis_rating' => 'Kost',
            'skor' => 1,
            'komentar' => 'Spam ulasan',
            'status' => 'Menunggu Validasi',
        ]);

        $response = $this->actingAs($this->pemilik)->patch(route('pemilik.rating.reject', $rating->id));
        $response->assertRedirect();

        $rating->refresh();
        $this->assertSame('Ditolak', $rating->status);
    }

    public function test_pemilik_can_reply_to_rating(): void
    {
        $rating = Rating::create([
            'user_id' => $this->penghuni->id,
            'jenis_rating' => 'Kost',
            'skor' => 5,
            'komentar' => 'Layanan ramah',
            'status' => 'Menunggu Validasi',
        ]);

        $response = $this->actingAs($this->pemilik)->post(route('pemilik.rating.balas', $rating->id), [
            'balasan' => 'Terima kasih banyak atas kunjungannya!',
            'setujui_sekaligus' => 1,
        ]);
        $response->assertRedirect();

        $rating->refresh();
        $this->assertSame('Terima kasih banyak atas kunjungannya!', $rating->balasan);
        $this->assertSame('Disetujui', $rating->status);
        $this->assertNotNull($rating->dibalas_pada);
    }

    public function test_pemilik_can_bulk_approve_ratings(): void
    {
        $rating1 = Rating::create([
            'user_id' => $this->penghuni->id,
            'jenis_rating' => 'Kost',
            'skor' => 4,
            'komentar' => 'Bagus 1',
            'status' => 'Menunggu Validasi',
        ]);

        $rating2 = Rating::create([
            'user_id' => $this->penghuni->id,
            'jenis_rating' => 'Kost',
            'skor' => 5,
            'komentar' => 'Bagus 2',
            'status' => 'Menunggu Validasi',
        ]);

        $response = $this->actingAs($this->pemilik)->post(route('pemilik.rating.bulk'), [
            'action' => 'approve',
            'rating_ids' => [$rating1->id, $rating2->id],
        ]);
        $response->assertRedirect();

        $this->assertSame('Disetujui', $rating1->fresh()->status);
        $this->assertSame('Disetujui', $rating2->fresh()->status);
    }

    public function test_pemilik_can_approve_all_pending_ratings(): void
    {
        Rating::create([
            'user_id' => $this->penghuni->id,
            'jenis_rating' => 'Kost',
            'skor' => 4,
            'komentar' => 'Ulasan 1',
            'status' => 'Menunggu Validasi',
        ]);

        Rating::create([
            'user_id' => $this->penghuni->id,
            'jenis_rating' => 'Kost',
            'skor' => 5,
            'komentar' => 'Ulasan 2',
            'status' => 'Menunggu Validasi',
        ]);

        $response = $this->actingAs($this->pemilik)->post(route('pemilik.rating.approve-all'));
        $response->assertRedirect();

        $this->assertSame(0, Rating::where('status', 'Menunggu Validasi')->count());
        $this->assertSame(2, Rating::where('status', 'Disetujui')->count());
    }
}
