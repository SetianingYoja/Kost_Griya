@extends('layouts.dashboard')

@section('title', 'Notifikasi')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="h3 fw-bold text-secondary mb-1">Notifikasi</h2>
        <p class="text-muted small mb-0">Informasi terbaru terkait proses kost dan aktivitas akun Anda.</p>
    </div>
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-outline-griya btn-sm">
                <i class="bi bi-check2-all me-1"></i> Tandai semua dibaca
            </button>
        </form>
    @endif
</div>

<div class="card-griya p-0 overflow-hidden">
    @forelse($notifications as $notification)
        @php($notificationData = $notification->data)
        <div class="p-3 p-md-4 border-bottom {{ $notification->read_at ? '' : 'bg-light' }}">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                <a href="{{ route('notifications.open', ['id' => $notification->id]) }}" class="text-decoration-none flex-grow-1">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="stat-icon flex-shrink-0" style="width: 40px; height: 40px; background: {{ $notification->read_at ? 'rgba(100, 116, 139, 0.1)' : 'rgba(37, 99, 235, 0.1)' }}; color: {{ $notification->read_at ? '#64748B' : 'var(--brand-primary)' }};">
                            <i class="bi {{ $notification->read_at ? 'bi-envelope-open' : 'bi-bell-fill' }}"></i>
                        </div>
                        <div>
                            <h5 class="h6 fw-bold text-secondary mb-1">{{ $notificationData['title'] ?? 'Notifikasi' }}</h5>
                            <p class="text-muted small mb-1">{{ $notificationData['message'] ?? '' }}</p>
                            <small class="text-muted">{{ $notification->created_at?->format('d M Y, H:i') }}</small>
                        </div>
                    </div>
                </a>
                @if(!$notification->read_at)
                    <form method="POST" action="{{ route('notifications.read', ['id' => $notification->id]) }}" class="flex-shrink-0">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-light border text-muted">
                            <i class="bi bi-check2 me-1"></i> Tandai dibaca
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="text-center py-5 px-3">
            <div class="stat-icon mx-auto mb-3" style="width: 56px; height: 56px; background: rgba(100, 116, 139, 0.1); color: #64748B;">
                <i class="bi bi-bell-slash fs-4"></i>
            </div>
            <h5 class="fw-bold text-secondary">Belum ada notifikasi</h5>
            <p class="text-muted small mb-0">Notifikasi proses akan muncul di halaman ini.</p>
        </div>
    @endforelse
</div>

@if($notifications->hasPages())
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
@endif
@endsection