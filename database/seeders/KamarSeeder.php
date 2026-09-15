<?php

namespace Database\Seeders;

use App\Models\Kamar;
use App\Models\TipeKamar;
use Illuminate\Database\Seeder;

class KamarSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tipe Kamar
        $standar = TipeKamar::firstOrCreate(
            ['slug' => 'standar'],
            [
                'nama_tipe' => 'Tipe Standar',
                'harga_bulanan' => 950000,
                'fasilitas_dasar' => 'Kasur Springbed (100x200), Lemari Pakaian Minimalis, Meja Belajar & Kursi, Kamar Mandi Luar Bersih, Kipas Angin Dinding, WiFi High-Speed',
                'deskripsi' => 'Pilihan tepat dan hemat untuk mahasiswi yang menginginkan hunian nyaman, tenang untuk belajar, dan sirkulasi udara optimal.',
            ]
        );

        $deluxe = TipeKamar::firstOrCreate(
            ['slug' => 'deluxe'],
            [
                'nama_tipe' => 'Tipe Deluxe',
                'harga_bulanan' => 1350000,
                'fasilitas_dasar' => 'AC Dingin 0.5 PK, Kamar Mandi Dalam (Shower & Kloset Duduk), Springbed Premium (120x200), Lemari 2 Pintu Cermin, Meja Kerja Ergonomis, WiFi High-Speed',
                'deskripsi' => 'Kamar modern dengan kamar mandi dalam dan penyejuk udara (AC), memberikan privasi maksimal dan kenyamanan prima setelah seharian beraktivitas.',
            ]
        );

        $vip = TipeKamar::firstOrCreate(
            ['slug' => 'vip-suite'],
            [
                'nama_tipe' => 'Tipe VIP Suite',
                'harga_bulanan' => 1850000,
                'fasilitas_dasar' => 'Smart TV 32 Inch, AC Inverter 1 PK, Kamar Mandi Dalam (Water Heater), Kulkas Mini Pribadi, Springbed Queen Size (160x200), Balkon Pribadi, Meja Rias & Kerja',
                'deskripsi' => 'Unit eksklusif terluas dengan fasilitas setaraf hotel bintang, air panas water heater, balkon menghadap pemandangan hijau, dan smart TV untuk hiburan santai.',
            ]
        );

        // 2. Daftar Kamar
        $rooms = [
            // Lantai 1
            [
                'tipe_kamar_id' => $standar->id,
                'nomor_kamar' => 'Kamar 101',
                'lantai' => 1,
                'harga' => 950000,
                'fasilitas' => 'Springbed Single, Meja Belajar, Lemari Baju, Kipas Dinding, Kamar Mandi Luar',
                'deskripsi' => 'Kamar lantai 1 dekat dengan ruang tamu bersama dan dapur utama.',
                'status' => 'Tersedia',
            ],
            [
                'tipe_kamar_id' => $standar->id,
                'nomor_kamar' => 'Kamar 102',
                'lantai' => 1,
                'harga' => 950000,
                'fasilitas' => 'Springbed Single, Meja Belajar, Lemari Baju, Kipas Dinding, Kamar Mandi Luar',
                'deskripsi' => 'Kamar lantai 1 dengan pencahayaan alami dan sirkulasi udara baik.',
                'status' => 'Tersedia',
            ],
            [
                'tipe_kamar_id' => $standar->id,
                'nomor_kamar' => 'Kamar 103',
                'lantai' => 1,
                'harga' => 950000,
                'fasilitas' => 'Springbed Single, Meja Belajar, Lemari Baju, Kipas Dinding, Kamar Mandi Luar',
                'deskripsi' => 'Kamar lantai 1 posisi tenang di ujung lorong.',
                'status' => 'Tersedia',
            ],
            // Lantai 2
            [
                'tipe_kamar_id' => $deluxe->id,
                'nomor_kamar' => 'Kamar 201',
                'lantai' => 2,
                'harga' => 1350000,
                'fasilitas' => 'AC 0.5 PK, Kamar Mandi Dalam (Shower), Springbed 120x200, Meja Belajar, Lemari Cermin',
                'deskripsi' => 'Kamar lantai 2 yang sejuk, hening, dan nyaman dengan kamar mandi dalam.',
                'status' => 'Tersedia',
            ],
            [
                'tipe_kamar_id' => $deluxe->id,
                'nomor_kamar' => 'Kamar 202',
                'lantai' => 2,
                'harga' => 1350000,
                'fasilitas' => 'AC 0.5 PK, Kamar Mandi Dalam (Shower), Springbed 120x200, Meja Belajar, Lemari Cermin',
                'deskripsi' => 'Kamar lantai 2 menghadap taman dalam yang asri.',
                'status' => 'Tersedia',
            ],
            [
                'tipe_kamar_id' => $deluxe->id,
                'nomor_kamar' => 'Kamar 203',
                'lantai' => 2,
                'harga' => 1350000,
                'fasilitas' => 'AC 0.5 PK, Kamar Mandi Dalam (Shower), Springbed 120x200, Meja Belajar, Lemari Cermin',
                'deskripsi' => 'Kamar lantai 2 dengan jendela luas dan pencahayaan matahari pagi.',
                'status' => 'Tersedia',
            ],
            // Lantai 3
            [
                'tipe_kamar_id' => $vip->id,
                'nomor_kamar' => 'Kamar 301',
                'lantai' => 3,
                'harga' => 1850000,
                'fasilitas' => 'AC 1 PK, Smart TV 32", Water Heater, Kulkas Mini, Queen Bed, Balkon Pribadi',
                'deskripsi' => 'Suite termewah lantai 3 dengan pemandangan pegunungan dan hembusan angin segar dari balkon.',
                'status' => 'Tersedia',
            ],
            [
                'tipe_kamar_id' => $vip->id,
                'nomor_kamar' => 'Kamar 302',
                'lantai' => 3,
                'harga' => 1850000,
                'fasilitas' => 'AC 1 PK, Smart TV 32", Water Heater, Kulkas Mini, Queen Bed, Balkon Pribadi',
                'deskripsi' => 'Suite elegan lantai 3 dengan interior modern dan kenyamanan ekstra.',
                'status' => 'Tersedia',
            ],
        ];

        foreach ($rooms as $room) {
            Kamar::firstOrCreate(
                ['nomor_kamar' => $room['nomor_kamar']],
                $room
            );
        }
    }
}
