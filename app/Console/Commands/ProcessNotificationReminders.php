<?php

namespace App\Console\Commands;

use App\Models\Sewa;
use App\Models\Tagihan;
use App\Notifications\BusinessNotification;
use App\Services\Notifications\NotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessNotificationReminders extends Command
{
    private const TIMEZONE = 'Asia/Jakarta';

    protected $signature = 'notifications:process-reminders';

    protected $description = 'Mengirim reminder tagihan dan masa sewa serta menandai tagihan terlambat';

    public function handle(NotificationService $notificationService): int
    {
        $today = CarbonImmutable::now(self::TIMEZONE)->startOfDay();

        $completedLeases = $this->completeExpiredLeases($today);
        $billingReminders = $this->sendBillingReminders($today, $notificationService);
        $leaseReminders = $this->sendLeaseReminders($today, $notificationService);
        $overdueBills = $this->markOverdueBills($today, $notificationService);

        $this->info(sprintf(
            'Sewa selesai: %d, reminder tagihan: %d, reminder sewa: %d, tagihan terlambat: %d.',
            $completedLeases,
            $billingReminders,
            $leaseReminders,
            $overdueBills
        ));

        return self::SUCCESS;
    }

    private function completeExpiredLeases(CarbonImmutable $today): int
    {
        $completed = 0;

        Sewa::query()
            ->where('status', 'Aktif')
            ->whereDate('tanggal_selesai', '<=', $today->toDateString())
            ->select('id')
            ->eachById(function (Sewa $candidate) use ($today, &$completed): void {
                DB::transaction(function () use ($candidate, $today, &$completed): void {
                    $sewa = Sewa::query()
                        ->whereKey($candidate->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $sewa || $sewa->status !== 'Aktif' || ! $sewa->tanggal_selesai || $sewa->tanggal_selesai->gt($today)) {
                        return;
                    }

                    $kamar = $sewa->kamar()->lockForUpdate()->first();

                    $sewa->status = 'Selesai';
                    $sewa->save();

                    if ($kamar && ! Sewa::query()
                        ->where('kamar_id', $kamar->id)
                        ->where('status', 'Aktif')
                        ->where('id', '!=', $sewa->id)
                        ->exists()) {
                        $kamar->status = 'Tersedia';
                        $kamar->save();
                    }

                    $completed++;
                });
            });

        return $completed;
    }

    private function sendBillingReminders(
        CarbonImmutable $today,
        NotificationService $notificationService,
    ): int {
        $sent = 0;

        foreach ([3 => 'h3', 1 => 'h1'] as $daysBefore => $period) {
            $dueDate = $today->addDays($daysBefore)->toDateString();

            $tagihans = Tagihan::with('user')
                ->where('status', '!=', 'Lunas')
                ->whereDate('tanggal_jatuh_tempo', $dueDate)
                ->get();

            foreach ($tagihans as $tagihan) {
                $notificationService->sendAfterCommit(
                    $tagihan->user,
                    new BusinessNotification(
                        'tagihan',
                        'reminder_'.$period,
                        'Pengingat jatuh tempo tagihan',
                        'Tagihan '.$tagihan->nomor_tagihan.' jatuh tempo dalam '.$daysBefore.' hari.',
                        'tagihan:'.$tagihan->id.':reminder:'.$period,
                        ['entity_type' => 'tagihan', 'entity_id' => $tagihan->id]
                    )
                );
                $sent++;
            }
        }

        return $sent;
    }

    private function sendLeaseReminders(
        CarbonImmutable $today,
        NotificationService $notificationService,
    ): int {
        $sent = 0;

        foreach ([7 => 'h7', 3 => 'h3'] as $daysBefore => $period) {
            $endDate = $today->addDays($daysBefore)->toDateString();

            $sewas = Sewa::with('user')
                ->where('status', 'Aktif')
                ->whereDate('tanggal_selesai', $endDate)
                ->get();

            foreach ($sewas as $sewa) {
                $notificationService->sendAfterCommit(
                    $sewa->user,
                    new BusinessNotification(
                        'sewa',
                        'reminder_'.$period,
                        'Pengingat masa sewa',
                        'Masa sewa Anda berakhir dalam '.$daysBefore.' hari.',
                        'sewa:'.$sewa->id.':reminder:'.$period,
                        ['entity_type' => 'sewa', 'entity_id' => $sewa->id]
                    )
                );
                $sent++;
            }
        }

        return $sent;
    }

    private function markOverdueBills(
        CarbonImmutable $today,
        NotificationService $notificationService,
    ): int {
        $updated = 0;

        $tagihans = Tagihan::with('user')
            ->whereNotIn('status', ['Lunas', 'Terlambat'])
            ->whereDate('tanggal_jatuh_tempo', '<', $today->toDateString())
            ->get();

        foreach ($tagihans as $tagihan) {
            DB::transaction(function () use ($tagihan): void {
                $tagihan->status = 'Terlambat';
                $tagihan->save();
            });

            $notification = new BusinessNotification(
                'tagihan',
                'terlambat',
                'Tagihan terlambat',
                'Tagihan '.$tagihan->nomor_tagihan.' telah melewati tanggal jatuh tempo.',
                'tagihan:'.$tagihan->id.':terlambat',
                ['entity_type' => 'tagihan', 'entity_id' => $tagihan->id]
            );

            $notificationService->sendAfterCommit($tagihan->user, $notification);
            $notificationService->sendToRoleAfterCommit('super-admin', $notification);
            $updated++;
        }

        return $updated;
    }
}
