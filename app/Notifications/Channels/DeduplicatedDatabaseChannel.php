<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;

class DeduplicatedDatabaseChannel extends DatabaseChannel
{
    protected function buildPayload($notifiable, Notification $notification)
    {
        if (! method_exists($notification, 'deduplicationKey')) {
            throw new \RuntimeException('Database notifications must define a deduplication key.');
        }

        return array_merge(parent::buildPayload($notifiable, $notification), [
            'deduplication_key' => $notification->deduplicationKey($notifiable),
        ]);
    }
}
