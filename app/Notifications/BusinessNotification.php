<?php

namespace App\Notifications;

use App\Notifications\Channels\DeduplicatedDatabaseChannel;
use Illuminate\Notifications\Notification;
use InvalidArgumentException;

class BusinessNotification extends Notification
{
    public function __construct(
        public readonly string $category,
        public readonly string $event,
        public readonly string $title,
        public readonly string $message,
        public readonly string $deduplicationKey,
        public readonly array $context = [],
    ) {
        if (strlen($this->deduplicationKey) > 191) {
            throw new InvalidArgumentException('The notification deduplication key may not exceed 191 bytes.');
        }
    }

    public function via(object $notifiable): array
    {
        return [DeduplicatedDatabaseChannel::class, 'broadcast'];
    }

    public function databaseType(object $notifiable): string
    {
        return $this->category.'.'.$this->event;
    }

    public function deduplicationKey(object $notifiable): string
    {
        return $this->deduplicationKey;
    }

    public function toDatabase(object $notifiable): array
    {
        return array_merge($this->context, [
            'category' => $this->category,
            'event' => $this->event,
            'title' => $this->title,
            'message' => $this->message,
            'deduplication_key' => $this->deduplicationKey,
        ]);
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    public function broadcastType(): string
    {
        return 'business.notification';
    }
}
