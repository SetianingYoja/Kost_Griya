<?php

namespace Database\Seeders;

use App\Models\KostInfo;
use Illuminate\Database\Seeder;

class KostInfoSeeder extends Seeder
{
    public function run(): void
    {
        KostInfo::firstOrCreate(
            ['id' => 1],
            [
                'nama_kost' => 'Kost Putri Griya Ayu',
                'tagline' => 'Modern Elegant Boarding House',
                'deskripsi' => 'Kost Putri Griya Ayu menyediakan hunian eksklusif, aman, nyaman, dan tenang bagi mahasiswi dan karyawati. Dilengkapi dengan fasilitas modern, keamanan 24 jam CCTV, WiFi super cepat, dan lingkungan yang asri di kawasan strategis Purwokerto, Banyumas',
                'alamat' => 'J62P+XVC, Jl. Kenanga 4, RT.8/RW.2, Sumampir Kulon, Sumampir, Kulon, Kabupaten Banyumas, Jawa Tengah 53125',
                'no_telepon' => '6282242645466',
                'email' => 'info@griyaayu.com',
                'bank_nama' => 'Bank Central Asia (BCA)',
                'bank_rekening' => '8415291039',
                'bank_atas_nama' => 'Kost Putri Griya Ayu',
                'aturan_kost' => "1. Khusus Putri (Mahasiswi & Karyawati).\n2. Jam malam tamu maksimal pukul 21.00 WIB di ruang tamu bersama.\n3. Tamu pria dilarang masuk ke area lorong/kamar pribadi.\n4. Menjaga kebersihan bersama di area dapur dan balkon.\n5. Dilarang merokok di dalam kamar dan membawa hewan peliharaan.",
            ]
        );
    }
}
