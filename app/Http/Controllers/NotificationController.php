<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(15);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(string $id)
    {
        $notification = $this->ownedNotification($id);
        $notification->markAsRead();

        return redirect()->route('notifications.index');
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->each->markAsRead();

        return redirect()->route('notifications.index');
    }

    public function open(string $id)
    {
        $notification = $this->ownedNotification($id);
        $notification->markAsRead();

        $route = $this->notificationRoute($notification);

        return $route
            ? redirect()->to($route)
            : redirect()->route('notifications.index');
    }

    private function ownedNotification(string $id): DatabaseNotification
    {
        return Auth::user()->notifications()
            ->whereKey($id)
            ->firstOrFail();
    }

    private function notificationRoute(DatabaseNotification $notification): ?string
    {
        $entityId = $notification->data['entity_id'] ?? null;
        $category = $notification->data['category'] ?? null;

        if (! is_numeric($entityId)) {
            return null;
        }

        if (Auth::user()->isPenghuni()) {
            $prefix = 'penghuni';
        } elseif (Auth::user()->isPemilik() || Auth::user()->isSuperAdmin()) {
            $prefix = 'pemilik';
        } else {
            return null;
        }
        $routeName = match ($category) {
            'booking' => $prefix.'.booking.show',
            'pembayaran' => $prefix.'.pembayaran.show',
            'perpanjangan' => $prefix.'.perpanjangan.show',
            'tagihan' => $prefix.'.tagihan.show',
            'keluhan' => $prefix.'.keluhan.show',
            default => null,
        };

        return $routeName ? route($routeName, ['id' => (int) $entityId]) : null;
    }
}
