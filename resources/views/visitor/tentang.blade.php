@extends('layouts.app')

@section('title', 'Tentang Kami — Kost Putri Griya Ayu')

@section('content')
<!-- Header Tentang Kami -->
<section class="py-5 bg-light border-bottom">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <span class="badge-tagline mb-2">Profil & Filosofi</span>
                <h1 class="display-5 fw-bold text-secondary mb-3">Tentang Kost Putri Griya Ayu</h1>
                <p class="lead text-muted">
                    Menghadirkan kenyamanan layaknya rumah sendiri dengan standar keamanan dan kebersihan penginapan modern di Kota Purwokerto.
                </p>
            </div>
            <div class="col-lg-5 text-center">
                <div class="p-4 rounded-4 bg-white border shadow-sm">
                    <div class="display-4 fw-bold text-primary mb-1">100%</div>
                    <p class="text-secondary fw-semibold mb-0">Khusus Putri & Lingkungan Kondusif</p>
                    <small class="text-muted">Aman, tenang, dan terjaga 24 jam penuh</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Visi & Keunggulan -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <div class="card-griya p-2">
                    <img src="{{ asset('images/kamar-default.svg') }}" alt="Griya Ayu Building" class="rounded-3 w-100" style="height: 350px; object-fit: cover;">
                </div>
            </div>
            <div class="col-lg-6">
                <h2 class="display-6 fw-bold text-secondary mb-3">Komitmen Kenyamanan Kami</h2>
                <p class="text-muted leading-relaxed mb-4">
                    {{ $kostInfo->deskripsi ?? 'Kost Putri Griya Ayu menyediakan hunian eksklusif, aman, nyaman, dan tenang bagi mahasiswi dan karyawati. Dilengkapi dengan fasilitas modern, keamanan 24 jam CCTV, WiFi super cepat, dan lingkungan yang asri di kawasan strategis Purwokerto, Banyumas' }}
                </p>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-patch-check-fill text-primary fs-4 mt-1"></i>
                        <div>
                            <strong class="text-secondary d-block">Privasi & Ketenangan Maksimal</strong>
                            <span class="text-muted small">Suasana hening yang mendukung konsentrasi belajar dan istirahat berkualitas setiap hari.</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-patch-check-fill text-primary fs-4 mt-1"></i>
                        <div>
                            <strong class="text-secondary d-block">Standar Kebersihan Tinggi</strong>
                            <span class="text-muted small">Area bersama seperti dapur, lorong, dan ruang santai dibersihkan secara rutin oleh staf kebersihan.</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <i class="bi bi-patch-check-fill text-primary fs-4 mt-1"></i>
                        <div>
                            <strong class="text-secondary d-block">Transparansi Finansial</strong>
                            <span class="text-muted small">Semua tagihan, riwayat bayar, dan perpanjangan tercatat transparan melalui portal aplikasi.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tata Tertib & Peraturan Kost -->
<section class="py-5 bg-light border-top border-bottom">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge-tagline mb-2">Tata Tertib</span>
            <h2 class="display-6 fw-bold text-secondary">Aturan & Ketertiban Kost</h2>
            <p class="text-muted">Dibuat demi kenyamanan, keselamatan, dan ketenteraman seluruh penghuni Kost Putri Griya Ayu.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-8">
                <div class="card-griya p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div class="stat-icon bg-light text-primary" style="width: 44px; height: 44px;">
                            <i class="bi bi-card-checklist fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-secondary mb-0">Aturan Resmi Hunian Kost</h5>
                            <small class="text-muted">Wajib dipatuhi oleh seluruh penyewa</small>
                        </div>
                    </div>

                    <div class="ps-2">
                        @if($kostInfo && $kostInfo->aturan_kost)
                            <div class="text-muted leading-relaxed" style="white-space: pre-line;">
                                {{ $kostInfo->aturan_kost }}
                            </div>
                        @else
                            <ol class="text-muted leading-relaxed small">
                                <li class="mb-2">Khusus Putri (Mahasiswi & Karyawati).</li>
                                <li class="mb-2">Jam malam penerimaan tamu maksimal pukul 21.00 WIB di ruang tamu bersama.</li>
                                <li class="mb-2">Tamu pria dilarang masuk ke dalam area lorong dan kamar pribadi penghuni.</li>
                                <li class="mb-2">Menjaga kebersihan bersama di area dapur, wastafel, dan jemuran.</li>
                                <li class="mb-2">Dilarang merokok di dalam kamar, mengonsumsi minuman keras, atau membawa obat-obatan terlarang.</li>
                                <li class="mb-2">Dilarang membawa hewan peliharaan.</li>
                                <li class="mb-2">Wajib mengunci pintu gerbang utama saat keluar masuk demi keselamatan bersama.</li>
                            </ol>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
