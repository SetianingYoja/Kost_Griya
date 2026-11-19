@extends('layouts.dashboard')

@section('title', 'Detail Kamar ' . $kamar->nomor_kamar)

@section('content')
<div class="mb-4">
    <a href="{{ route('pengelola.kamar.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kamar
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Rincian {{ $kamar->nomor_kamar }}</h3>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-griya p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <h4 class="fw-bold text-secondary mb-0">{{ $kamar->nomor_kamar }}</h4>
                <span class="badge bg-{{ $kamar->status === 'Tersedia' ? 'success' : ($kamar->status === 'Sedang Diproses' ? 'warning text-dark' : ($kamar->status === 'Terisi' ? 'danger' : 'secondary')) }} px-3 py-2 fs-6">
                    {{ $kamar->status }}
                </span>
            </div>

            <div class="row g-3 small mb-4">
                <div class="col-sm-4">
                    <span class="text-muted d-block">Tipe Kamar:</span>
                    <strong>{{ $kamar->tipeKamar->nama_tipe ?? '-' }}</strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted d-block">Lantai:</span>
                    <strong>Lantai {{ $kamar->lantai }}</strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted d-block">Tarif Sewa:</span>
                    <strong class="text-primary fs-5">Rp {{ number_format($kamar->harga, 0, ',', '.') }}</strong> / bln
                </div>
            </div>

            <div class="mb-3">
                <h6 class="fw-bold text-secondary mb-1">Fasilitas Kamar:</h6>
                <p class="text-muted small">{{ $kamar->fasilitas ?: 'Belum ada data fasilitas tercatat.' }}</p>
            </div>

            <div class="mb-3">
                <h6 class="fw-bold text-secondary mb-1">Deskripsi:</h6>
                <p class="text-muted small leading-relaxed">{{ $kamar->deskripsi ?: 'Tidak ada catatan deskripsi khusus.' }}</p>
            </div>

            <div class="d-flex gap-2 pt-3 border-top">
                <a href="{{ route('pengelola.kamar.edit', $kamar->id) }}" class="btn btn-primary-griya btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit Data Kamar
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-kamar"
                        data-kamar-id="{{ $kamar->id }}"
                        data-nomor-kamar="{{ $kamar->nomor_kamar }}"
                        data-preview-url="{{ route('pengelola.kamar.preview-destroy', $kamar->id) }}">
                    <i class="bi bi-trash me-1"></i> Hapus Kamar & Data Terkait
                </button>
            </div>
        </div>

        <!-- Penyewa Aktif -->
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-person-badge text-primary me-2"></i>Penyewa Saat Ini</h5>
            @if($kamar->currentSewa)
                <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-secondary mb-1">{{ $kamar->currentSewa->user->name }}</h6>
                        <span class="small text-muted d-block"><i class="bi bi-whatsapp text-success me-1"></i> {{ $kamar->currentSewa->user->phone }}</span>
                        <span class="small text-muted"><i class="bi bi-calendar me-1"></i> {{ $kamar->currentSewa->tanggal_mulai->format('d M Y') }} s/d {{ $kamar->currentSewa->tanggal_selesai->format('d M Y') }}</span>
                    </div>
                    <a href="{{ route('pengelola.penghuni.show', $kamar->currentSewa->user_id) }}" class="btn btn-outline-griya btn-sm">
                        Profil Penyewa
                    </a>
                </div>
            @else
                <p class="text-muted small mb-0">Saat ini tidak ada penyewa aktif di kamar ini.</p>
            @endif
        </div>
    </div>

    <!-- Foto Kamar -->
    <div class="col-lg-4">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3">Foto Kamar</h5>
            @if($kamar->galleryImages->count())
                <div class="row g-2">
                    @foreach($kamar->galleryImages as $galleryImage)
                        <div class="col-6">
                            <img src="{{ $galleryImage->foto_url }}" alt="{{ $kamar->nomor_kamar }}" class="img-fluid rounded border" style="height: 110px; object-fit: cover; width: 100%;">
                        </div>
                    @endforeach
                </div>
            @else
                <img src="{{ $kamar->foto_url }}" alt="{{ $kamar->nomor_kamar }}" class="img-fluid rounded border mb-3">
            @endif
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Kamar & Data Terkait -->
<div class="modal fade" id="modalHapusKamar" tabindex="-1" aria-labelledby="modalHapusKamarTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="formHapusKamar" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="modalHapusKamarTitle">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Kamar & Seluruh Data Terkait
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Loading Spinner -->
                    <div id="modalHapusLoading" class="text-center py-4">
                        <div class="spinner-border text-danger mb-2" role="status">
                            <span class="visually-hidden">Memuat data...</span>
                        </div>
                        <p class="text-muted small mb-0">Sedang memeriksa seluruh relasi data kamar...</p>
                    </div>

                    <!-- Detail Konten (disembunyikan saat loading) -->
                    <div id="modalHapusContent" style="display: none;">
                        <div class="alert alert-danger d-flex align-items-start gap-2 mb-3">
                            <i class="bi bi-shield-exclamation fs-4 flex-shrink-0"></i>
                            <div>
                                <strong class="d-block">PERINGATAN: Tindakan Ini Bersifat Permanen!</strong>
                                <span class="small">Kamar beserta seluruh riwayat transaksi, sewa, tagihan, pembayaran, keluhan, dan ulasan yang berhubungan dengan kamar ini akan dibersihkan secara total dari sistem. Tindakan ini tidak dapat dibatalkan.</span>
                            </div>
                        </div>

                        <!-- Info Ringkas Kamar -->
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="row g-2 small">
                                <div class="col-sm-4">
                                    <span class="text-muted d-block">Nomor Kamar:</span>
                                    <strong id="previewNomorKamar" class="fs-6 text-secondary">-</strong>
                                </div>
                                <div class="col-sm-4">
                                    <span class="text-muted d-block">Lantai:</span>
                                    <strong id="previewLantai" class="fs-6 text-secondary">-</strong>
                                </div>
                                <div class="col-sm-4">
                                    <span class="text-muted d-block">Tipe Kamar:</span>
                                    <strong id="previewTipeKamar" class="fs-6 text-secondary">-</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Rincian Data Terkait yang Akan Dihapus -->
                        <h6 class="fw-bold text-secondary mb-2 small text-uppercase">Rincian Data Terkait yang Akan Ikut Dihapus:</h6>
                        <div class="row g-2 mb-3 small">
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-white shadow-sm">
                                    <span class="text-muted d-block">Booking</span>
                                    <span id="previewCountBookings" class="fw-bold fs-5 text-dark">0</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-white shadow-sm">
                                    <span class="text-muted d-block">Data Sewa</span>
                                    <span id="previewCountSewas" class="fw-bold fs-5 text-dark">0</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-white shadow-sm">
                                    <span class="text-muted d-block">Tagihan</span>
                                    <span id="previewCountTagihans" class="fw-bold fs-5 text-dark">0</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-white shadow-sm">
                                    <span class="text-muted d-block">Perpanjangan</span>
                                    <span id="previewCountPerpanjangans" class="fw-bold fs-5 text-dark">0</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-white shadow-sm">
                                    <span class="text-muted d-block">Pembayaran</span>
                                    <span id="previewCountPembayarans" class="fw-bold fs-5 text-dark">0</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-white shadow-sm">
                                    <span class="text-muted d-block">Keluhan</span>
                                    <span id="previewCountKeluhans" class="fw-bold fs-5 text-dark">0</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-white shadow-sm">
                                    <span class="text-muted d-block">Rating / Ulasan</span>
                                    <span id="previewCountRatings" class="fw-bold fs-5 text-dark">0</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="border rounded p-2 text-center bg-white shadow-sm">
                                    <span class="text-muted d-block">Foto Kamar</span>
                                    <span id="previewCountFotos" class="fw-bold fs-5 text-dark">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Analisis Keamanan Akun Penghuni -->
                        <div class="border rounded p-3 bg-light mb-2">
                            <h6 class="fw-bold text-secondary mb-2 small"><i class="bi bi-people me-1"></i>Analisis Keamanan Akun Penghuni:</h6>
                            <div class="mb-2">
                                <span class="badge bg-danger me-1">Akan Dihapus</span>
                                <strong id="previewUsersToDeleteCount">0</strong> akun penghuni (hanya memiliki relasi di kamar ini tanpa data kamar lain).
                                <div id="previewUsersToDeleteList" class="text-muted small mt-1 ps-2"></div>
                            </div>
                            <div>
                                <span class="badge bg-success me-1">Tetap Dipertahankan</span>
                                <strong id="previewUsersToPreserveCount">0</strong> akun penghuni (memiliki riwayat sewa/booking di kamar lain).
                                <div id="previewUsersToPreserveList" class="text-muted small mt-1 ps-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm" id="btnSubmitHapusKamar" disabled>
                        <i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Kamar & Seluruh Data Terkait
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalHapusKamar');
    if (!modalEl) return;
    const modalHapus = new bootstrap.Modal(modalEl);
    const formHapus = document.getElementById('formHapusKamar');
    const loadingEl = document.getElementById('modalHapusLoading');
    const contentEl = document.getElementById('modalHapusContent');
    const btnSubmit = document.getElementById('btnSubmitHapusKamar');

    document.querySelectorAll('.btn-hapus-kamar').forEach(btn => {
        btn.addEventListener('click', function () {
            const kamarId = this.dataset.kamarId;
            const previewUrl = this.dataset.previewUrl;

            formHapus.action = `/pengelola/kamar/${kamarId}`;
            loadingEl.style.display = 'block';
            contentEl.style.display = 'none';
            btnSubmit.disabled = true;

            modalHapus.show();

            fetch(previewUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                document.getElementById('previewNomorKamar').textContent = data.kamar.nomor_kamar;
                document.getElementById('previewLantai').textContent = 'Lantai ' + data.kamar.lantai;
                document.getElementById('previewTipeKamar').textContent = data.kamar.tipe_kamar;

                document.getElementById('previewCountBookings').textContent = data.counts.bookings;
                document.getElementById('previewCountSewas').textContent = data.counts.sewas;
                document.getElementById('previewCountTagihans').textContent = data.counts.tagihans;
                document.getElementById('previewCountPerpanjangans').textContent = data.counts.perpanjangans;
                document.getElementById('previewCountPembayarans').textContent = data.counts.pembayarans;
                document.getElementById('previewCountKeluhans').textContent = data.counts.keluhans;
                document.getElementById('previewCountRatings').textContent = data.counts.ratings;
                document.getElementById('previewCountFotos').textContent = data.counts.fotos;

                document.getElementById('previewUsersToDeleteCount').textContent = data.counts.users_to_delete;
                const delList = document.getElementById('previewUsersToDeleteList');
                if (data.users_to_delete.length > 0) {
                    delList.innerHTML = data.users_to_delete.map(u => `<div>• <strong>${u.name}</strong> (${u.email})</div>`).join('');
                } else {
                    delList.innerHTML = '<span class="fst-italic text-muted">Tidak ada akun penghuni yang akan dihapus.</span>';
                }

                document.getElementById('previewUsersToPreserveCount').textContent = data.counts.users_to_preserve;
                const presList = document.getElementById('previewUsersToPreserveList');
                if (data.users_to_preserve.length > 0) {
                    presList.innerHTML = data.users_to_preserve.map(u => `<div>• <strong>${u.name}</strong> (${u.email}) — <em>${u.reason}</em></div>`).join('');
                } else {
                    presList.innerHTML = '<span class="fst-italic text-muted">Tidak ada akun penghuni yang perlu dipertahankan.</span>';
                }

                loadingEl.style.display = 'none';
                contentEl.style.display = 'block';
                btnSubmit.disabled = false;
            })
            .catch(err => {
                loadingEl.innerHTML = '<div class="alert alert-danger mb-0">Gagal memuat pratinjau data kamar. Silakan coba kembali.</div>';
            });
        });
    });
});
</script>
@endpush
