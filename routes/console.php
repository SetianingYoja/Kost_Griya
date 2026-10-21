<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notifications:process-reminders')
    ->dailyAt('00:05')
    ->timezone('Asia/Jakarta');

// Generate monthly invoices for approved lease extensions
Schedule::command('perpanjangan:generate-tagihan')
    ->dailyAt('01:00')
    ->timezone('Asia/Jakarta');

// Expire unpaid bookings past payment deadline
Schedule::command('bookings:expire')
    ->dailyAt('02:00')
    ->timezone('Asia/Jakarta');
