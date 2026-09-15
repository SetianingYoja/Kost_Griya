# Rancang Bangun Sistem Informasi Manajemen Kost Putri Griya Ayu (PRD v1.0)

Dokumen ini memuat rencana implementasi menyeluruh untuk Sistem Informasi Manajemen Kost Berbasis Web **Kost Putri Griya Ayu** sesuai dokumen PRD v1.0, dengan fokus khusus hanya pada direktori project `C:\wamp64\www\kost_griya`.

---

## User Review Required

> [!IMPORTANT]
> **Fokus dan Lokasi Project**
> Sesuai instruksi Anda, kami **hanya** akan bekerja di dalam direktori workspace `C:\wamp64\www\kost_griya` dan tidak akan mengakses, menyalin langsung, atau mengubah direktori backup `C:\wamp64\www\SIM-Kost`.
>
> **Koneksi Database**
> Database MySQL `kost` telah terdeteksi aktif pada `localhost:3306`. Pada eksekusi Milestone 1, kita akan mengonfigurasi `.env` dan menjalankan migrasi serta seeder terstruktur lengkap untuk menyiapkan skema relasi dan data awal.

> [!NOTE]
> **Keputusan Bisnis Model B (Perpanjangan & DP)**
> PRD menetapkan **Model B**: Penghuni membayar DP terlebih dahulu untuk perpanjangan, kemudian tagihan bulanan tetap berjalan dengan DP diperhitungkan (tidak terjadi double charge). Kita akan mengonfigurasi default DP (misalnya 30% atau nominal fleksibel yang dapat diatur oleh Pemilik saat menyetujui pengajuan perpanjangan) agar alur bisnis berjalan dinamis dan tidak kaku.

---

## Open Questions

1. **Default Akun Awal (Seeder)**:
   Apakah Anda menyetujui akun bawaan seeder berikut untuk pengujian awal?
   - Super Admin: `superadmin@griyaayu.com` / `password123`
   - Pemilik Kost: `pemilik@griyaayu.com` / `password123`
   - Penghuni Demo: `penghuni@griyaayu.com` / `password123`
   *(Password di-hash dengan Bcrypt).*

2. **Metode Pembayaran Transfer Manual**:
   Apakah nomor rekening bank default untuk transfer manual dapat kita buatkan informasi rekening resmi Kost Griya Ayu (contoh: BCA / Mandiri a.n. Pemilik Kost Griya Ayu)?

---

## Proposed Architecture & Design System

### 1. Visual & UI Guidelines (Sesuai PRD Section 22)
- **Brand**: Griya Ayu — Modern Elegant Boarding House
- **Color Palette**:
  - Primary: `#2563EB` (Royal Blue)
  - Secondary: `#1E3A8A` (Deep Navy)
  - Light Accent: `#EFF6FF`
  - Background: `#F8FAFC`
  - Card/Surface: `#FFFFFF`
  - Typography Dark: `#0F172A`
  - Status Colors: Sukses `#10B981`, Peringatan `#F59E0B`, Bahaya `#EF4444`, Info `#3B82F6`
- **Typography**: Google Fonts **Poppins** (UI/teks umum) + **Crimson Text** (headings/judul elegan).
- **Iconography**: Bootstrap Icons (`bi-*`). Tidak menggunakan emoji sebagai icon antarmuka utama.

---

## Proposed Changes by Milestone

### Milestone 1 — Project Foundation & Database RBAC
Mempersiapkan fondasi Laravel 12 di `C:\wamp64\www\kost_griya`, konfigurasi environment, migrasi tabel lengkap, model Eloquent, middleware autentikasi dan otorisasi.

#### [NEW] Inisialisasi Laravel 12
- Instalasi kerangka kerja Laravel 12 di direktori `C:\wamp64\www\kost_griya`.
- Konfigurasi `.env` ke database `kost`, timezone `Asia/Jakarta`, APP_NAME `Kost Putri Griya Ayu`.
- Setup Vite, Bootstrap 5, Bootstrap Icons, Google Fonts (Poppins, Crimson Text).

#### [NEW] Database Migrations
- `0001_01_01_000000_create_users_table.php` (dengan kolom `role_id`, `phone`, `status`, `avatar`).
- `create_roles_table.php` (id, name, slug, description).
- `create_permissions_table.php` (id, name, slug, module, description).
- `create_role_permissions_table.php` (role_id, permission_id).
- `create_tipe_kamar_table.php` (id, nama_tipe, harga_dasar, fasilitas_umum, deskripsi).
- `create_kamar_table.php` (id, tipe_kamar_id, nomor_kamar, lantai, harga, fasilitas, deskripsi, foto, status: Tersedia / Terisi / Tidak tersedia).
- `create_kost_info_table.php` (id, nama_kost, tagline, deskripsi, alamat, kontak_wa, email, nomor_rekening, aturan).
- `create_bookings_table.php` (id, user_id, kamar_id, kode_booking, tanggal_mulai, durasi_bulan, catatan, status: Menunggu Validasi, Disetujui, Menunggu Pembayaran, Ditolak, Kadaluarsa, Dibatalkan, Selesai, alasan_penolakan).
- `create_sewas_table.php` (kontrak aktif: id, user_id, kamar_id, booking_id, tanggal_mulai, tanggal_selesai, status: Aktif, Selesai).
- `create_tagihans_table.php` (id, user_id, kamar_id, nomor_tagihan, periode, nominal, tanggal_jatuh_tempo, status: Belum Dibayar, Menunggu Validasi, Lunas, Terlambat).
- `create_pembayarans_table.php` (id, user_id, booking_id, tagihan_id, perpanjangan_id, kode_transaksi, jenis_pembayaran: Booking Awal, Tagihan Bulanan, DP Perpanjangan, Pelunasan Perpanjangan, nominal, bukti_pembayaran, status: Menunggu Validasi, Lunas, Ditolak, catatan_admin, tanggal_bayar).
- `create_perpanjangans_table.php` (id, sewa_id, user_id, durasi_bulan, tanggal_mulai_baru, tanggal_selesai_baru, nominal_total, nominal_dp, status: Menunggu Validasi, Disetujui, Menunggu Pembayaran DP, DP Dibayar, Aktif, Ditolak, catatan).
- `create_keluhans_table.php` (id, user_id, kamar_id, judul, deskripsi, foto, status: Menunggu, Diproses, Selesai, Ditolak, tanggapan, tanggal_selesai).
- `create_ratings_table.php` (id, user_id, keluhan_id, jenis_rating: Kost / Penanganan Keluhan, skor 1-5, ulasan).
- `create_riwayat_aktivitas_table.php` (id, user_id, judul, deskripsi, tipe, created_at).

#### [NEW] Seeders
- `DatabaseSeeder.php`, `RolePermissionSeeder.php`, `UserSeeder.php`, `KamarSeeder.php`, `KostInfoSeeder.php`.

#### [NEW] Models & Relationships
- `User`, `Role`, `Permission`, `TipeKamar`, `Kamar`, `KostInfo`, `Booking`, `Sewa`, `Tagihan`, `Pembayaran`, `Perpanjangan`, `Keluhan`, `Rating`, `RiwayatAktivitas`.

#### [NEW] Middleware Autentikasi & RBAC
- `RoleMiddleware.php`: Memeriksa apakah user memiliki role `Super Admin`, `Pemilik Kost`, atau `Penghuni`.
- `EnsureActiveUser.php`: Memeriksa status akun user `Aktif`.

---

### Milestone 2 — Visitor Website
Membangun tampilan publik yang modern, elegan, clean, dan profesional layaknya situs penginapan/hotel premium.

#### [NEW] Layout & Views Visitor
- [NEW] `resources/views/layouts/app.blade.php`: Header transparan/solid, navigasi elegan, footer informatif dengan kontak WhatsApp, alamat, dan quick links.
- [NEW] `resources/views/visitor/home.blade.php`: Hero section dengan identitas Kost Putri Griya Ayu, keunggulan fasilitas (Aman, Nyaman, Bersih, Strategis), kamar unggulan, lokasi Google Maps, kontak, dan CTA Booking.
- [NEW] `resources/views/visitor/kamar/index.blade.php`: Katalog kamar lengkap dengan filter tipe kamar & status ketersediaan, card kamar premium dengan harga dan badge status.
- [NEW] `resources/views/visitor/kamar/detail.blade.php`: Galeri foto kamar, detail spesifikasi, daftar fasilitas lengkap, deskripsi, aturan kost, dan tombol booking interaktif.
- [NEW] `resources/views/visitor/tentang.blade.php`: Profil Griya Ayu, nilai pelayanan, foto fasilitas umum kost putri.

#### [NEW] Autentikasi
- [NEW] `resources/views/auth/register.blade.php`: Form registrasi dengan validasi ketat (nama lengkap, email unik, nomor WhatsApp, password min. 8 karakter, konfirmasi). Default role = Penghuni.
- [NEW] `resources/views/auth/login.blade.php`: Form login elegan dengan feedback error yang aman. Redirect otomatis ke dashboard sesuai role masing-masing (Super Admin, Pemilik, Penghuni).
- [NEW] `app/Http/Controllers/AuthController.php`: Logic register, login, redirect role, logout.

---

### Milestone 3 — Booking System
Menyediakan alur pemesanan kamar terstruktur: Pengunjung -> Login -> Booking -> Validasi Pemilik.

#### [NEW] Controller & Views Booking
- [NEW] `app/Http/Controllers/Penghuni/BookingController.php`:
  - Form booking kamar dengan pilihan tanggal mulai sewa dan durasi sewa.
  - Validasi: Kamar hanya bisa dibooking jika berstatus `Tersedia`.
  - Simpan booking dengan status awal `Menunggu Validasi`.
- [NEW] `app/Http/Controllers/Pemilik/BookingApprovalController.php`:
  - Daftar permohonan booking baru.
  - Aksi persetujuan: Mengubah status menjadi `Menunggu Pembayaran`.
  - Aksi penolakan: Mengubah status menjadi `Ditolak` beserta catatan alasan penolakan.

---

### Milestone 4 — Pembayaran & Tagihan (Billing)
Mengelola bukti transfer, verifikasi pemilik, update otomatis status kamar menjadi `Terisi`, dan pengelolaan tagihan bulanan.

#### [NEW] Controller & Views Pembayaran & Tagihan
- [NEW] `app/Http/Controllers/Penghuni/PembayaranController.php`:
  - Tampilan instruksi pembayaran (rekening bank Griya Ayu, nominal pas, countdown/batas waktu).
  - Upload bukti transfer (JPG/PNG/PDF).
  - Status pembayaran berubah ke `Menunggu Validasi`.
- [NEW] `app/Http/Controllers/Pemilik/PembayaranValidationController.php`:
  - Pemilik memeriksa detail bukti transfer.
  - Approve: Pembayaran `Lunas`, otomatis membuat data `Sewa` (kontrak aktif), status Kamar berubah menjadi `Terisi`, role penghuni aktif menyewa.
  - Reject: Pembayaran `Ditolak` dengan feedback jelas; penghuni dapat mengunggah bukti perbaikan.
- [NEW] `app/Http/Controllers/Penghuni/TagihanController.php` & `Pemilik/TagihanController.php`:
  - Daftar tagihan bulanan.
  - Penghuni membayar tagihan bulanan dengan upload bukti.
  - Pemilik memvalidasi tagihan.

---

### Milestone 5 — Tenant Dashboard & Perpanjangan (Model B)
Mengelola dashboard interaktif penghuni sesuai state hidup sewa, dan pengajuan perpanjangan berbasis DP.

#### [NEW] Controller & Views Dashboard Penghuni
- [NEW] `app/Http/Controllers/Penghuni/DashboardController.php`:
  - Logika state dinamis:
    1. *Belum Booking*: Pesan ramah + CTA pesan kamar.
    2. *Menunggu Validasi Booking*: Status card interaktif.
    3. *Menunggu Pembayaran*: Total tagihan awal, countdown, form upload.
    4. *Aktif Menyewa*: Informasi kamar, sisa hari sewa, tagihan berjalan, tombol keluhan & perpanjangan.
    5. *Masa Sewa Berakhir*: Riwayat sewa, penawaran sewa ulang tanpa menghapus akun.
- [NEW] `app/Http/Controllers/Penghuni/PerpanjanganController.php`:
  - Pengajuan perpanjangan sewa (pilih durasi bulan).
  - Pemilik menyetujui dan menetapkan kewajiban DP (Model B).
  - Penghuni membayar DP -> Pemilik validasi DP -> Masa sewa diperpanjang -> Tagihan sisa berjalan otomatis tanpa double charge.

---

### Milestone 6 — Keluhan & Rating
Menyediakan modul komplain layanan kost serta rating kepuasan.

#### [NEW] Controller & Views Keluhan & Rating
- [NEW] `app/Http/Controllers/Penghuni/KeluhanController.php`: Form pembuatan keluhan (judul, deskripsi, foto keluhan).
- [NEW] `app/Http/Controllers/Pemilik/KeluhanController.php`: Pemilik memproses keluhan (`Menunggu` -> `Diproses` -> `Selesai` / `Ditolak`) dan menyertakan tanggapan tertulis.
- [NEW] `app/Http/Controllers/Penghuni/RatingController.php`:
  - Rating kost (1–5 bintang + review).
  - Rating penanganan keluhan (1–5 bintang) yang aktif setelah keluhan dinyatakan `Selesai`.

---

### Milestone 7 — Dashboard Pemilik & Laporan Operasional
Dashboard operasional untuk pemilik kost mengontrol seluruh aktivitas kost.

#### [NEW] Controller & Views Pemilik
- [NEW] `app/Http/Controllers/Pemilik/DashboardController.php`: Statistik real-time (Total Kamar, Kamar Tersedia, Kamar Terisi, Total Penghuni, Booking Menunggu, Pembayaran Menunggu, Tagihan Belum Lunas, Keluhan Menunggu).
- [NEW] `app/Http/Controllers/Pemilik/KamarController.php`: CRUD kamar (tambah foto, tipe, fasilitas, harga, nomor, status).
- [NEW] `app/Http/Controllers/Pemilik/PenghuniController.php`: Data seluruh penghuni aktif dan riwayat penghuni lama.
- [NEW] `app/Http/Controllers/Pemilik/LaporanController.php`: Laporan pendapatan keuangan, okupansi kamar, dan laporan keluhan/kepuasan.

---

### Milestone 8 — Super Admin & User Management
Pengelolaan sistem tingkat tinggi, RBAC, dan audit.

#### [NEW] Controller & Views Super Admin
- [NEW] `app/Http/Controllers/Admin/DashboardController.php`: Statistik global sistem.
- [NEW] `app/Http/Controllers/Admin/UserController.php`: CRUD pengguna, toggle status aktif/nonaktif, penugasan role.
- [NEW] `app/Http/Controllers/Admin/RolePermissionController.php`: Matriks hak akses role & permission sesuai Section 18 PRD.
- [NEW] `app/Http/Controllers/Admin/LaporanSistemController.php`: Rekap aktivitas dan laporan sistem menyeluruh.

---

## Verification Plan

### Automated Tests
1. **Artisan Syntax & Route Checks**:
   - `php artisan route:list`
   - `php artisan test` (Unit/Feature tests untuk Auth, Booking, Pembayaran, Role access)
2. **Database Integrity**:
   - `php artisan migrate:status`
   - Validasi foreign key constraints dan relasi seeder.

### Manual Verification
1. **Visitor Flow**:
   - Mengunjungi `/`, memeriksa responsiveness, tipografi Crimson Text & Poppins, palette warna `#2563EB`.
   - Mengunjungi katalog `/kamar` dan detail `/kamar/{id}`.
2. **Autentikasi & Registrasi**:
   - Mendaftar akun baru sebagai penghuni; verifikasi role otomatis `Penghuni`.
   - Menguji login untuk 3 role berbeda: Super Admin, Pemilik Kost, Penghuni.
3. **End-to-End Rental Cycle**:
   - Penghuni booking kamar -> Pemilik approve booking.
   - Penghuni upload bukti transfer -> Pemilik validasi lunas -> Kamar status berubah `Terisi`, penghuni berstatus `Aktif Menyewa`.
   - Penghuni melihat tagihan bulanan & mengajukan perpanjangan dengan DP (Model B).
   - Penghuni submit keluhan -> Pemilik menangani hingga `Selesai` -> Penghuni mengisi rating bintang 1–5.
4. **Super Admin Authorization**:
   - Memastikan Pemilik Kost tidak memiliki akses ke menu User Management / Role / Permission.
   - Memastikan Super Admin dapat mengelola user dan konfigurasi role.
