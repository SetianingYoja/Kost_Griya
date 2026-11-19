@extends('layouts.dashboard')

@section('title', 'Manajemen Kamar Kost')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Manajemen Data Kamar Kost</h3>
        <p class="text-muted small mb-0">Kelola nomor kamar, tipe kamar, tarif sewa, fasilitas, dan status ketersediaan</p>
    </div>
    <a href="{{ route('pengelola.kamar.create') }}" class="btn btn-primary-griya btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Tambah Kamar Baru
    </a>
</div>

<!-- Filter Bar -->
<div class="card-griya p-3 mb-4">
    <form action="{{ route('pengelola.kamar.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nomor kamar..." value="{{ request('q') }}">
        </div>
        <div class="col-md-3">
            <select name="tipe" class="form-select form-select-sm">
                <option value="">Semua Tipe Kamar</option>
                @foreach($tipeKamars as $t)
                    <option value="{{ $t->id }}" {{ request('tipe') == $t->id ? 'selected' : '' }}>{{ $t->nama_tipe }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="Tersedia" {{ request('status') == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                <option value="Sedang Diproses" {{ request('status') == 'Sedang Diproses' ? 'selected' : '' }}>Sedang Diproses</option>
                <option value="Terisi" {{ request('status') == 'Terisi' ? 'selected' : '' }}>Terisi</option>
                <option value="Tidak tersedia" {{ request('status') == 'Tidak tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary-griya flex-grow-1">Filter</button>
            <a href="{{ route('pengelola.kamar.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Foto</th>
                    <th>Nomor Kamar</th>
                    <th>Tipe</th>
                    <th>Lantai</th>
                    <th>Tarif / Bulan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kamars as $kamar)
                    <tr>
                        <td style="width: 70px;">
                            <img src="{{ $kamar->foto_url }}" alt="{{ $kamar->nomor_kamar }}" class="rounded" style="width: 54px; height: 40px; object-fit: cover;">
                        </td>
                        <td><strong>{{ $kamar->nomor_kamar }}</strong></td>
                        <td>{{ $kamar->tipeKamar->nama_tipe ?? '-' }}</td>
                        <td>Lantai {{ $kamar->lantai }}</td>
                        <td><strong>Rp {{ number_format($kamar->harga, 0, ',', '.') }}</strong></td>
                        <td>
                            @if($kamar->status === 'Tersedia')
                                <span class="badge bg-success">Tersedia</span>
                            @elseif($kamar->status === 'Sedang Diproses')
                                <span class="badge bg-warning text-dark">Sedang Diproses</span>
                            @elseif($kamar->status === 'Terisi')
                                <span class="badge bg-danger">Terisi</span>
                            @else
                                <span class="badge bg-secondary">Tidak Tersedia</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('pengelola.kamar.show', $kamar->id) }}" class="btn btn-sm btn-outline-griya py-0 px-2" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('pengelola.kamar.edit', $kamar->id) }}" class="btn btn-sm btn-light border py-0 px-2" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 btn-hapus-kamar" 
                                        data-kamar-id="{{ $kamar->id }}" 
                                        data-nomor-kamar="{{ $kamar->nomor_kamar }}"
                                        data-preview-url="{{ route('pengelola.kamar.preview-destroy', $kamar->id) }}"
                                        title="Hapus Kamar & Seluruh Data Terkait">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data kamar kost.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $kamars->links('pagination::bootstrap-5') }}
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
