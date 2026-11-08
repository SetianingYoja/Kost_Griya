<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Notifications\BusinessNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public function sendToRoleAfterCommit(
        string $roleSlug,
        BusinessNotification $notification,
        ?int $excludeUserId = null,
    ): void {
        $normalizedSlug = User::normalizeRoleSlug($roleSlug);

        $users = User::query()
            ->where('status', 'Aktif')
            ->whereHas('role', function ($query) use ($normalizedSlug): void {
                $query->where('slug', $normalizedSlug);
            })
            ->when($excludeUserId, function ($query) use ($excludeUserId): void {
                $query->where('id', '!=', $excludeUserId);
            })
            ->get();

        // Transisi aman: jika menargetkan 'pengelola' namun belum ada user ber-role pengelola di DB,
        // alihkan sementara ke role operasional lama 'pemilik-kost' agar notifikasi tidak hilang sebelum migrasi dijalankan.
        if ($users->isEmpty() && $normalizedSlug === 'pengelola') {
            $users = User::query()
                ->where('status', 'Aktif')
                ->whereHas('role', function ($query): void {
                    $query->where('slug', 'pemilik-kost');
                })
                ->when($excludeUserId, function ($query) use ($excludeUserId): void {
                    $query->where('id', '!=', $excludeUserId);
                })
                ->get();
        }

        $this->sendToUsersAfterCommit($users, $notification);
    }

    public function sendToUsersAfterCommit(iterable $users, BusinessNotification $notification): void
    {
        foreach ($users as $user) {
            $this->sendAfterCommit($user, $notification);
        }
    }

    public function sendAfterCommit(User $notifiable, BusinessNotification $notification): void
    {
        DB::afterCommit(function () use ($notifiable, $notification): void {
            $this->sendIfNotDuplicate($notifiable, $notification);
        });
    }

    private function sendIfNotDuplicate(User $notifiable, BusinessNotification $notification): void
    {
        $alreadySent = DB::table('notifications')
            ->where('notifiable_type', $notifiable->getMorphClass())
            ->where('notifiable_id', $notifiable->getKey())
            ->where('deduplication_key', $notification->deduplicationKey)
            ->exists();

        if ($alreadySent) {
            return;
        }

        try {
            Notification::sendNow($notifiable, $notification);
        } catch (QueryException $exception) {
            if (! $this->isDuplicateNotification($exception)) {
                throw $exception;
            }
        }
    }

    private function isDuplicateNotification(QueryException $exception): bool
    {
        return str_contains(
            $exception->getMessage(),
            'notifications_notifiable_deduplication_unique'
        );
    }
}
