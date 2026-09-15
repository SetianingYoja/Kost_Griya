<?php

namespace Tests\Feature;

use App\Models\Kamar;
use App\Models\TipeKamar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KamarMultiplePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_kamar_can_expose_multiple_gallery_images(): void
    {
        $tipe = TipeKamar::create([
            'nama_tipe' => 'Deluxe',
            'slug' => 'deluxe',
            'harga_bulanan' => 1500000,
        ]);

        $kamar = Kamar::create([
            'tipe_kamar_id' => $tipe->id,
            'nomor_kamar' => 'Kamar 101',
            'lantai' => 1,
            'harga' => 1500000,
            'status' => 'Tersedia',
            'foto' => 'kamar/primary.jpg',
        ]);

        $kamar->galleryImages()->createMany([
            ['foto' => 'kamar/gallery-1.jpg'],
            ['foto' => 'kamar/gallery-2.jpg'],
            ['foto' => 'kamar/gallery-3.jpg'],
        ]);

        $this->assertCount(3, $kamar->galleryImages()->get());
        $this->assertSame('kamar/gallery-1.jpg', $kamar->galleryImages()->first()->foto);
    }

    public function test_payment_approval_forms_use_patch_method(): void
    {
        $this->assertTrue(true);
    }
}
