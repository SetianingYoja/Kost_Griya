@extends('layouts.dashboard')

@section('title', 'Rating & Ulasan Kepuasan Penghuni')

@push('styles')
<style>
    .btn-action-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.15s ease-in-out;
        border: 1px solid transparent;
        font-size: 15px;
    }
    .btn-action-approve {
        color: #16a34a;
        background-color: rgba(22, 163, 74, 0.12);
    }
    .btn-action-approve:hover {
        color: #ffffff;
        background-color: #16a34a;
    }
    .btn-action-reject {
        color: #dc2626;
        background-color: rgba(220, 38, 38, 0.12);
    }
    .btn-action-reject:hover {
        color: #ffffff;
        background-color: #dc2626;
    }
    .btn-action-reply {
        color: #2563eb;
        background-color: rgba(37, 99, 235, 0.12);
    }
    .btn-action-reply:hover {
        color: #ffffff;
        background-color: #2563eb;
    }
    .bulk-toolbar {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 8px 14px;
        display: none;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }
    .bulk-toolbar.active {
        display: flex;
    }
    .nav-pills-griya .nav-link {
        color: #475569;
        font-weight: 500;
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
    }
    .nav-pills-griya .nav-link.active {
        background-color: var(--brand-primary, #2563eb);
        color: #ffffff;
        border-color: var(--brand-primary, #2563eb);
    }
    .nav-pills-griya .nav-link .badge {
        font-size: 0.725rem;
    }
</style>
@endpush

@section('content')
<!-- Header Page -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Rating & Ulasan Kepuasan Penghuni</h3>
        <p class="text-muted small mb-0">Moderasi ulasan penghuni dan kelola tanggapan sebelum ditampilkan ke publik</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if($countMenunggu > 0)
            <form action="{{ route('pemilik.rating.approve-all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin langsung menyetujui seluruh {{ $countMenunggu }} ulasan yang sedang menunggu validasi?');">
                @csrf
                <button type="submit" class="btn btn-sm btn-success d-inline-flex align-items-center gap-2 shadow-sm px-3">
                    <i class="bi bi-check-all fs-6"></i>
                    <span>Setujui Semua ({{ $countMenunggu }})</span>
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Keterangan Moderasi Publik -->
<div class="alert alert-info d-flex align-items-center gap-3 p-3 mb-4 rounded-3 border-0 bg-info-subtle text-info-emphasis shadow-sm">
    <i class="bi bi-shield-check fs-2 text-info flex-shrink-0"></i>
    <div class="small">
        <strong class="d-block mb-1 text-dark">Ketentuan Tampilan Website Publik:</strong>
        Hanya rating dan ulasan dengan status <span class="badge bg-success">Disetujui</span> yang akan tampil pada website publik. Balasan Pemilik Kost akan ditampilkan secara publik tepat di bawah ulasan penghuni. Ulasan yang masih menunggu validasi atau ditolak tidak akan terlihat oleh publik.
    </div>
</div>

<!-- Score Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Menunggu Validasi</span>
                <h3 class="fw-bold text-warning mb-0"><i class="bi bi-hourglass-split me-1"></i>{{ $countMenunggu }}</h3>
                <small class="text-muted">Perlu tindakan pemilik</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Rata-rata Rating Kost</span>
                <h3 class="fw-bold text-warning mb-0"><i class="bi bi-star-fill me-1"></i>{{ number_format($avgKost, 1) }} / 5.0</h3>
                <small class="text-muted">Dari ulasan disetujui</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Rata-rata Penanganan Keluhan</span>
                <h3 class="fw-bold text-warning mb-0"><i class="bi bi-star-fill me-1"></i>{{ number_format($avgKeluhan, 1) }} / 5.0</h3>
                <small class="text-muted">Respon keluhan teknis</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Total Semua Ulasan</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $countSemua }}</h3>
                <small class="text-muted">{{ $countDisetujui }} disetujui, {{ $countDitolak }} ditolak</small>
            </div>
        </div>
    </div>
</div>

<!-- Tab Filter Moderasi Status -->
<div class="d-flex flex-wrap gap-2 mb-3 nav-pills-griya">
    <a href="{{ route('pemilik.rating.index', array_merge(request()->except(['status', 'page']), ['status' => 'Menunggu Validasi'])) }}" 
       class="nav-link {{ $status === 'Menunggu Validasi' ? 'active' : '' }}">
        <i class="bi bi-hourglass-split me-1"></i> Menunggu Validasi 
        <span class="badge {{ $status === 'Menunggu Validasi' ? 'bg-light text-primary' : 'bg-warning text-dark' }} ms-1">{{ $countMenunggu }}</span>
    </a>
    <a href="{{ route('pemilik.rating.index', array_merge(request()->except(['status', 'page']), ['status' => 'Disetujui'])) }}" 
       class="nav-link {{ $status === 'Disetujui' ? 'active' : '' }}">
        <i class="bi bi-check-circle me-1"></i> Disetujui 
        <span class="badge {{ $status === 'Disetujui' ? 'bg-light text-primary' : 'bg-success' }} ms-1">{{ $countDisetujui }}</span>
    </a>
    <a href="{{ route('pemilik.rating.index', array_merge(request()->except(['status', 'page']), ['status' => 'Ditolak'])) }}" 
       class="nav-link {{ $status === 'Ditolak' ? 'active' : '' }}">
        <i class="bi bi-x-circle me-1"></i> Ditolak 
        <span class="badge {{ $status === 'Ditolak' ? 'bg-light text-primary' : 'bg-danger' }} ms-1">{{ $countDitolak }}</span>
    </a>
    <a href="{{ route('pemilik.rating.index', request()->except(['status', 'page'])) }}" 
       class="nav-link {{ empty($status) ? 'active' : '' }}">
        <i class="bi bi-collection me-1"></i> Semua Ulasan 
        <span class="badge {{ empty($status) ? 'bg-light text-primary' : 'bg-secondary' }} ms-1">{{ $countSemua }}</span>
    </a>
</div>

<!-- Filter Box -->
<div class="card-griya p-3 p-md-4 mb-4">
    <form method="GET" action="{{ route('pemilik.rating.index') }}" class="row g-3 align-items-end">
        @if($status)
            <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-secondary">Cari Penghuni / Ulasan</label>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Nama penghuni atau kata kunci..." value="{{ $search ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-secondary">Kategori Penilaian</label>
            <select name="jenis" class="form-select form-select-sm">
                <option value="">Semua Kategori</option>
                <option value="Kost" {{ ($jenis ?? '') === 'Kost' ? 'selected' : '' }}>Fasilitas Kost</option>
                <option value="Penanganan Keluhan" {{ ($jenis ?? '') === 'Penanganan Keluhan' ? 'selected' : '' }}>Penanganan Keluhan</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold text-secondary">Dari Tanggal</label>
            <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold text-secondary">Sampai Tanggal</label>
            <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate ?? '' }}">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary-griya btn-sm flex-fill">Cari</button>
            <a href="{{ route('pemilik.rating.index', $status ? ['status' => $status] : []) }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<!-- Bulk Action Form & Toolbar -->
<form id="bulkForm" action="{{ route('pemilik.rating.bulk') }}" method="POST">
    @csrf
    <input type="hidden" name="action" id="bulkActionInput" value="approve">

    <div id="bulkToolbar" class="bulk-toolbar shadow-sm">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check2-square text-primary fs-5"></i>
            <span class="small fw-semibold text-secondary"><span id="selectedCount">0</span> ulasan terpilih</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" onclick="submitBulk('approve')" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-check-lg"></i> Setujui yang Dipilih
            </button>
            <button type="button" onclick="submitBulk('reject')" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 bg-white">
                <i class="bi bi-x-lg"></i> Tolak yang Dipilih
            </button>
            <button type="button" onclick="deselectAll()" class="btn btn-sm btn-link text-muted text-decoration-none">
                Batal
            </button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card-griya p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;" class="text-center">
                            <input type="checkbox" id="checkAll" class="form-check-input" title="Pilih Semua">
                        </th>
                        <th style="min-width: 140px;">Penghuni</th>
                        <th style="min-width: 140px;">Kategori & Skor</th>
                        <th style="min-width: 250px;">Ulasan & Balasan</th>
                        <th style="min-width: 120px;">Status Moderasi</th>
                        <th style="min-width: 100px;">Tanggal</th>
                        <th style="min-width: 110px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ratings as $rating)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" name="rating_ids[]" value="{{ $rating->id }}" class="form-check-input row-checkbox" onchange="updateBulkToolbar()">
                            </td>
                            <td>
                                <strong class="d-block text-secondary">{{ $rating->user->name ?? 'Penghuni' }}</strong>
                                <small class="text-muted">
                                    @if($rating->kamar)
                                        Kamar {{ $rating->kamar->nomor_kamar }}
                                    @elseif($rating->keluhan && $rating->keluhan->kamar)
                                        Kamar {{ $rating->keluhan->kamar->nomor_kamar }}
                                    @else
                                        -
                                    @endif
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border mb-1">{{ $rating->jenis_rating }}</span>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $rating->skor ? '-fill' : '' }}"></i>
                                    @endfor
                                    <strong class="text-secondary ms-1">({{ $rating->skor }}/5)</strong>
                                </div>
                                @if($rating->keluhan)
                                    <small class="text-muted d-block mt-1">Keluhan: {{ Str::limit($rating->keluhan->judul, 25) }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="text-secondary mb-1">
                                    {{ $rating->komentar ?: 'Tidak ada teks ulasan.' }}
                                </div>
                                @if($rating->balasan)
                                    <div class="mt-2 p-2 bg-light rounded-2 border-start border-3 border-primary small">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-semibold text-primary">
                                                <i class="bi bi-reply-fill me-1"></i>Balasan Pemilik Kost:
                                            </span>
                                            @if($rating->dibalas_pada)
                                                <small class="text-muted">{{ $rating->dibalas_pada->format('d/m/Y') }}</small>
                                            @endif
                                        </div>
                                        <div class="text-muted">{{ $rating->balasan }}</div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($rating->status === 'Disetujui')
                                    <span class="badge bg-success d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-check-circle"></i> Disetujui
                                    </span>
                                @elseif($rating->status === 'Ditolak')
                                    <span class="badge bg-danger d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-x-circle"></i> Ditolak
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-hourglass-split"></i> Menunggu Validasi
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div>{{ $rating->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $rating->created_at->format('H:i') }}</small>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <!-- Ikon ✓ = Setujui -->
                                    @if($rating->status !== 'Disetujui')
                                        <button type="button" class="btn btn-action-icon btn-action-approve" title="Setujui Ulasan" onclick="submitSingleAction('approve', {{ $rating->id }}, '{{ addslashes($rating->user->name ?? 'penghuni') }}')">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    @endif

                                    <!-- Ikon ✕ = Tolak -->
                                    @if($rating->status !== 'Ditolak')
                                        <button type="button" class="btn btn-action-icon btn-action-reject" title="Tolak Ulasan" onclick="submitSingleAction('reject', {{ $rating->id }}, '{{ addslashes($rating->user->name ?? 'penghuni') }}')">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    @endif

                                    <!-- Ikon 💬 = Balas -->
                                    <button type="button" class="btn btn-action-icon btn-action-reply" title="Balas Ulasan" 
                                            onclick="openBalasModal({{ $rating->id }}, '{{ addslashes($rating->user->name ?? 'Penghuni') }}', '{{ addslashes($rating->komentar ?? '') }}', {{ $rating->skor }}, '{{ addslashes($rating->balasan ?? '') }}', '{{ $rating->status }}')">
                                        <i class="bi bi-chat-dots-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-chat-square-quote fs-2 text-muted d-block mb-2"></i>
                                Belum ada data ulasan rating yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $ratings->links('pagination::bootstrap-5') }}
        </div>
    </div>
</form>

<!-- Single Action Hidden Forms -->
<form id="singleApproveForm" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<form id="singleRejectForm" method="POST" style="display: none;">
    @csrf
    @method('PATCH')
</form>

<!-- Modal Balas Ulasan -->
<div class="modal fade" id="modalBalas" tabindex="-1" aria-labelledby="modalBalasTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formBalas" method="POST">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-secondary" id="modalBalasTitle">
                        <i class="bi bi-chat-dots text-primary me-2"></i>Balas Ulasan Penghuni
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Ringkasan Ulasan -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong id="modalNamaPenghuni" class="text-secondary small">-</strong>
                            <div id="modalBintang" class="text-warning small"></div>
                        </div>
                        <p id="modalKomentarPenghuni" class="small text-muted mb-0 fst-italic">-</p>
                    </div>

                    <!-- Input Balasan -->
                    <div class="mb-3">
                        <label for="inputBalasan" class="form-label small fw-semibold text-secondary">
                            Tuliskan Balasan Pemilik Kost <span class="text-danger">*</span>
                        </label>
                        <textarea name="balasan" id="inputBalasan" rows="4" class="form-control" placeholder="Tuliskan respon terima kasih atau apresiasi terhadap masukan penghuni..." required maxlength="1000"></textarea>
                        <div class="form-text small">Balasan ini akan tampil di website publik tepat di bawah ulasan penghuni.</div>
                    </div>

                    <div class="form-check" id="wrapperSetujuiSekaligus">
                        <input class="form-check-input" type="checkbox" name="setujui_sekaligus" value="1" id="checkSetujuiSekaligus" checked>
                        <label class="form-check-label small text-secondary" for="checkSetujuiSekaligus">
                            Sekaligus <strong>setujui ulasan ini</strong> agar langsung tampil di website publik
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-griya btn-sm">
                        <i class="bi bi-send me-1"></i> Simpan & Kirim Balasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Master checkbox toggle
    const checkAll = document.getElementById('checkAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkToolbar = document.getElementById('bulkToolbar');
    const selectedCountSpan = document.getElementById('selectedCount');
    const bulkForm = document.getElementById('bulkForm');
    const bulkActionInput = document.getElementById('bulkActionInput');

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            rowCheckboxes.forEach(cb => cb.checked = checkAll.checked);
            updateBulkToolbar();
        });
    }

    function updateBulkToolbar() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        if (selectedCountSpan) {
            selectedCountSpan.textContent = checkedCount;
        }

        if (bulkToolbar) {
            if (checkedCount > 0) {
                bulkToolbar.classList.add('active');
            } else {
                bulkToolbar.classList.remove('active');
            }
        }

        if (checkAll && rowCheckboxes.length > 0) {
            checkAll.checked = (checkedCount === rowCheckboxes.length);
        }
    }

    function deselectAll() {
        if (checkAll) checkAll.checked = false;
        rowCheckboxes.forEach(cb => cb.checked = false);
        updateBulkToolbar();
    }

    function submitBulk(action) {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        if (checkedCount === 0) {
            alert('Silakan centang minimal satu ulasan terlebih dahulu.');
            return;
        }

        const actionText = action === 'approve' ? 'menyetujui' : 'menolak';
        if (confirm(`Apakah Anda yakin ingin ${actionText} ${checkedCount} ulasan yang dipilih?`)) {
            bulkActionInput.value = action;
            bulkForm.submit();
        }
    }

    function submitSingleAction(action, id, nama) {
        if (action === 'approve') {
            if (confirm(`Setujui ulasan dari ${nama}? Ulasan ini akan langsung tampil di website publik.`)) {
                const form = document.getElementById('singleApproveForm');
                form.action = `/pemilik/rating/${id}/approve`;
                form.submit();
            }
        } else if (action === 'reject') {
            if (confirm(`Tolak ulasan dari ${nama}? Ulasan tidak akan tampil di website publik.`)) {
                const form = document.getElementById('singleRejectForm');
                form.action = `/pemilik/rating/${id}/reject`;
                form.submit();
            }
        }
    }

    function openBalasModal(id, nama, komentar, skor, balasan, status) {
        const formBalas = document.getElementById('formBalas');
        formBalas.action = `/pemilik/rating/${id}/balas`;

        document.getElementById('modalNamaPenghuni').textContent = nama || 'Penghuni';
        document.getElementById('modalKomentarPenghuni').textContent = komentar ? `"${komentar}"` : 'Tidak ada komentar teks.';
        document.getElementById('inputBalasan').value = balasan || '';

        // Generate bintang
        let starsHtml = '';
        for (let i = 1; i <= 5; i++) {
            starsHtml += `<i class="bi bi-star${i <= skor ? '-fill' : ''}"></i>`;
        }
        starsHtml += ` <strong class="text-secondary ms-1">(${skor}/5)</strong>`;
        document.getElementById('modalBintang').innerHTML = starsHtml;

        const checkSetujui = document.getElementById('checkSetujuiSekaligus');
        const wrapperSetujui = document.getElementById('wrapperSetujuiSekaligus');
        if (status === 'Disetujui') {
            wrapperSetujui.style.display = 'none';
            checkSetujui.checked = false;
        } else {
            wrapperSetujui.style.display = 'block';
            checkSetujui.checked = true;
        }

        const modal = new bootstrap.Modal(document.getElementById('modalBalas'));
        modal.show();
    }
</script>
@endpush
