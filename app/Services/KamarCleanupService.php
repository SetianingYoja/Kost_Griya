<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\KamarFoto;
use App\Models\Keluhan;
use App\Models\Pembayaran;
use App\Models\Perpanjangan;
use App\Models\Rating;
use App\Models\RiwayatAktivitas;
use App\Models\Sewa;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KamarCleanupService
{
    /**
     * Dapatkan rincian seluruh data yang terkait dengan kamar sebelum dilakukan penghapusan.
     *
     * @return array<string, mixed>
     */
    public function preview(Kamar $kamar): array
    {
        $kamarId = $kamar->id;

        $bookingIds = Booking::where('kamar_id', $kamarId)->pluck('id')->all();
        $sewaIds = Sewa::where('kamar_id', $kamarId)->pluck('id')->all();
        $perpanjanganIds = Perpanjangan::whereIn('sewa_id', $sewaIds)->pluck('id')->all();

        $tagihanIds = Tagihan::where('kamar_id', $kamarId)
            ->orWhereIn('sewa_id', $sewaIds)
            ->orWhereIn('perpanjangan_id', $perpanjanganIds)
            ->pluck('id')
            ->all();

        $pembayaranIds = Pembayaran::whereIn('booking_id', $bookingIds)
            ->orWhereIn('tagihan_id', $tagihanIds)
            ->orWhereIn('perpanjangan_id', $perpanjanganIds)
            ->pluck('id')
            ->all();

        $keluhanIds = Keluhan::where('kamar_id', $kamarId)->pluck('id')->all();

        $ratingIds = Rating::where('kamar_id', $kamarId)
            ->orWhereIn('sewa_id', $sewaIds)
            ->orWhereIn('keluhan_id', $keluhanIds)
            ->pluck('id')
            ->all();

        $fotoIds = KamarFoto::where('kamar_id', $kamarId)->pluck('id')->all();
        $totalFotos = count($fotoIds) + ($kamar->foto ? 1 : 0);

        // Analisis akun pengguna
        $candidateUserIds = collect()
            ->merge(Booking::whereIn('id', $bookingIds)->pluck('user_id'))
            ->merge(Sewa::whereIn('id', $sewaIds)->pluck('user_id'))
            ->merge(Keluhan::whereIn('id', $keluhanIds)->pluck('user_id'))
            ->merge(Rating::whereIn('id', $ratingIds)->pluck('user_id'))
            ->unique()
            ->filter()
            ->values();

        $usersToDelete = [];
        $usersToPreserve = [];

        foreach ($candidateUserIds as $userId) {
            $user = User::with('role')->find($userId);
            if (! $user) {
                continue;
            }

            $classification = $this->evaluateUserSafety($user, $kamarId, $sewaIds, $bookingIds, $tagihanIds, $perpanjanganIds, $pembayaranIds, $keluhanIds, $ratingIds);

            if ($classification['safe_to_delete']) {
                $usersToDelete[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'reason' => $classification['reason'],
                ];
            } else {
                $usersToPreserve[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'reason' => $classification['reason'],
                ];
            }
        }

        return [
            'kamar' => [
                'id' => $kamar->id,
                'nomor_kamar' => $kamar->nomor_kamar,
                'lantai' => $kamar->lantai,
                'tipe_kamar' => $kamar->tipeKamar->nama_tipe ?? 'Standar',
                'status' => $kamar->status,
                'harga' => (float) $kamar->harga,
            ],
            'counts' => [
                'bookings' => count($bookingIds),
                'sewas' => count($sewaIds),
                'tagihans' => count($tagihanIds),
                'perpanjangans' => count($perpanjanganIds),
                'pembayarans' => count($pembayaranIds),
                'keluhans' => count($keluhanIds),
                'ratings' => count($ratingIds),
                'fotos' => $totalFotos,
                'users_to_delete' => count($usersToDelete),
                'users_to_preserve' => count($usersToPreserve),
            ],
            'users_to_delete' => $usersToDelete,
            'users_to_preserve' => $usersToPreserve,
        ];
    }

    /**
     * Hapus kamar beserta seluruh data terkait secara aman dalam DB::transaction.
     *
     * @return array<string, mixed>
     */
    public function destroyWithRelated(Kamar $kamar, User $actor): array
    {
        $kamarId = $kamar->id;
        $nomorKamar = $kamar->nomor_kamar;
        $filesToDelete = [];
        $summary = [];

        DB::transaction(function () use ($kamarId, $nomorKamar, $actor, &$filesToDelete, &$summary) {
            // Kunci kamar untuk mencegah mutasi concurrent
            $lockedKamar = Kamar::where('id', $kamarId)->lockForUpdate()->firstOrFail();

            // 1. Kumpulkan seluruh ID terkait dengan lockForUpdate
            $bookingIds = Booking::where('kamar_id', $kamarId)->lockForUpdate()->pluck('id')->all();
            $sewaIds = Sewa::where('kamar_id', $kamarId)->lockForUpdate()->pluck('id')->all();
            $perpanjanganIds = Perpanjangan::whereIn('sewa_id', $sewaIds)->lockForUpdate()->pluck('id')->all();

            $tagihanIds = Tagihan::where('kamar_id', $kamarId)
                ->orWhereIn('sewa_id', $sewaIds)
                ->orWhereIn('perpanjangan_id', $perpanjanganIds)
                ->lockForUpdate()
                ->pluck('id')
                ->all();

            $pembayaranIds = Pembayaran::whereIn('booking_id', $bookingIds)
                ->orWhereIn('tagihan_id', $tagihanIds)
                ->orWhereIn('perpanjangan_id', $perpanjanganIds)
                ->lockForUpdate()
                ->pluck('id')
                ->all();

            $keluhanIds = Keluhan::where('kamar_id', $kamarId)->lockForUpdate()->pluck('id')->all();

            $ratingIds = Rating::where('kamar_id', $kamarId)
                ->orWhereIn('sewa_id', $sewaIds)
                ->orWhereIn('keluhan_id', $keluhanIds)
                ->lockForUpdate()
                ->pluck('id')
                ->all();

            $fotoIds = KamarFoto::where('kamar_id', $kamarId)->lockForUpdate()->pluck('id')->all();

            // 2. Evaluasi akun pengguna yang terkait
            $candidateUserIds = collect()
                ->merge(Booking::whereIn('id', $bookingIds)->pluck('user_id'))
                ->merge(Sewa::whereIn('id', $sewaIds)->pluck('user_id'))
                ->merge(Keluhan::whereIn('id', $keluhanIds)->pluck('user_id'))
                ->merge(Rating::whereIn('id', $ratingIds)->pluck('user_id'))
                ->unique()
                ->filter()
                ->values();

            $userIdsToDelete = [];
            $preservedUserNames = [];
            $deletedUserNames = [];

            foreach ($candidateUserIds as $userId) {
                $user = User::with('role')->where('id', $userId)->lockForUpdate()->first();
                if (! $user) {
                    continue;
                }

                $classification = $this->evaluateUserSafety($user, $kamarId, $sewaIds, $bookingIds, $tagihanIds, $perpanjanganIds, $pembayaranIds, $keluhanIds, $ratingIds);

                if ($classification['safe_to_delete']) {
                    $userIdsToDelete[] = $user->id;
                    $deletedUserNames[] = $user->name;
                } else {
                    $preservedUserNames[] = $user->name.' ('.$classification['reason'].')';
                }
            }

            // 3. Kumpulkan file storage yang aman untuk dihapus (tanpa menghapus sebelum commit)
            $this->collectStorageFiles($kamarId, $pembayaranIds, $keluhanIds, $fotoIds, $lockedKamar->foto, $filesToDelete);

            // 4. Urutan penghapusan database sesuai foreign key constraints:
            // Langkah 1: Pembayaran (leaf node yang merujuk booking, tagihan, perpanjangan)
            $countPembayaran = 0;
            if (! empty($pembayaranIds)) {
                $countPembayaran = Pembayaran::whereIn('id', $pembayaranIds)->delete();
            }

            // Langkah 2: Rating (merujuk kamar, sewa, keluhan)
            $countRating = 0;
            if (! empty($ratingIds)) {
                $countRating = Rating::whereIn('id', $ratingIds)->delete();
            }

            // Langkah 3: Keluhan (merujuk kamar)
            $countKeluhan = 0;
            if (! empty($keluhanIds)) {
                $countKeluhan = Keluhan::whereIn('id', $keluhanIds)->delete();
            }

            // Langkah 4: Tagihan (merujuk kamar_id [RESTRICT] dan sewa_id)
            $countTagihan = 0;
            if (! empty($tagihanIds)) {
                $countTagihan = Tagihan::whereIn('id', $tagihanIds)->delete();
            }

            // Langkah 5: Perpanjangan (merujuk sewa_id)
            $countPerpanjangan = 0;
            if (! empty($perpanjanganIds)) {
                $countPerpanjangan = Perpanjangan::whereIn('id', $perpanjanganIds)->delete();
            }

            // Langkah 6: Sewa (merujuk kamar_id [RESTRICT] dan booking_id)
            $countSewa = 0;
            if (! empty($sewaIds)) {
                $countSewa = Sewa::whereIn('id', $sewaIds)->delete();
            }

            // Langkah 7: Booking (merujuk kamar_id [RESTRICT])
            $countBooking = 0;
            if (! empty($bookingIds)) {
                $countBooking = Booking::whereIn('id', $bookingIds)->delete();
            }

            // Langkah 8: Foto Galeri Kamar
            $countFoto = 0;
            if (! empty($fotoIds)) {
                $countFoto = KamarFoto::whereIn('id', $fotoIds)->delete();
            }
            if ($lockedKamar->foto) {
                $countFoto++;
            }

            // Langkah 9: Kamar (semua foreign key RESTRICT telah bersih)
            $lockedKamar->delete();

            // Langkah 10: Hapus akun pengguna yang 100% aman dihapus
            $countUsersDeleted = 0;
            if (! empty($userIdsToDelete)) {
                // Hapus notifikasi & aktivitas pengguna tersebut terlebih dahulu
                DB::table('notifications')
                    ->where('notifiable_type', User::class)
                    ->whereIn('notifiable_id', $userIdsToDelete)
                    ->delete();

                RiwayatAktivitas::whereIn('user_id', $userIdsToDelete)->delete();

                $countUsersDeleted = User::whereIn('id', $userIdsToDelete)->delete();
            }

            // 5. Simpan catatan audit riwayat aktivitas untuk Super Admin yang melakukan penghapusan
            $auditDeskripsi = sprintf(
                'Super Admin %s (ID: %d) menghapus kamar %s beserta seluruh data terkait: %d booking, %d sewa, %d tagihan, %d perpanjangan, %d pembayaran, %d keluhan, %d rating, %d foto. Akun penghuni dihapus: %d (%s). Akun penghuni dipertahankan: %d (%s).',
                $actor->name,
                $actor->id,
                $nomorKamar,
                $countBooking,
                $countSewa,
                $countTagihan,
                $countPerpanjangan,
                $countPembayaran,
                $countKeluhan,
                $countRating,
                $countFoto,
                $countUsersDeleted,
                empty($deletedUserNames) ? 'Tidak ada' : implode(', ', $deletedUserNames),
                count($preservedUserNames),
                empty($preservedUserNames) ? 'Tidak ada' : implode(', ', $preservedUserNames)
            );

            RiwayatAktivitas::catat(
                $actor->id,
                'Pembersihan Data Kamar',
                $auditDeskripsi,
                'warning'
            );

            $summary = [
                'nomor_kamar' => $nomorKamar,
                'counts' => [
                    'bookings' => $countBooking,
                    'sewas' => $countSewa,
                    'tagihans' => $countTagihan,
                    'perpanjangans' => $countPerpanjangan,
                    'pembayarans' => $countPembayaran,
                    'keluhans' => $countKeluhan,
                    'ratings' => $countRating,
                    'fotos' => $countFoto,
                    'users_deleted' => $countUsersDeleted,
                    'users_preserved' => count($preservedUserNames),
                ],
                'deleted_users' => $deletedUserNames,
                'preserved_users' => $preservedUserNames,
            ];
        });

        // 6. Eksekusi penghapusan file storage HANYA setelah DB transaction berhasil commit
        $this->deleteStorageFiles($filesToDelete);

        return $summary;
    }

    /**
     * Evaluasi apakah akun pengguna aman untuk dihapus.
     *
     * @param  array<int>  $sewaIds
     * @param  array<int>  $bookingIds
     * @param  array<int>  $tagihanIds
     * @param  array<int>  $perpanjanganIds
     * @param  array<int>  $pembayaranIds
     * @param  array<int>  $keluhanIds
     * @param  array<int>  $ratingIds
     * @return array{safe_to_delete: bool, reason: string}
     */
    protected function evaluateUserSafety(
        User $user,
        int $kamarId,
        array $sewaIds,
        array $bookingIds,
        array $tagihanIds,
        array $perpanjanganIds,
        array $pembayaranIds,
        array $keluhanIds,
        array $ratingIds
    ): array {
        // Jangan pernah hapus staf / admin / pemilik kost
        $roleSlug = $user->role ? User::normalizeRoleSlug($user->role->slug) : '';
        if ($roleSlug !== 'penghuni') {
            return [
                'safe_to_delete' => false,
                'reason' => 'Pengguna memiliki role '.$roleSlug.' (bukan penghuni)',
            ];
        }

        // 1. Cek apakah memiliki sewa di kamar lain
        $otherSewaCount = Sewa::where('user_id', $user->id)
            ->where('kamar_id', '!=', $kamarId)
            ->count();
        if ($otherSewaCount > 0) {
            return [
                'safe_to_delete' => false,
                'reason' => 'Memiliki '.$otherSewaCount.' data sewa di kamar lain',
            ];
        }

        // 2. Cek apakah memiliki booking di kamar lain
        $otherBookingCount = Booking::where('user_id', $user->id)
            ->where('kamar_id', '!=', $kamarId)
            ->count();
        if ($otherBookingCount > 0) {
            return [
                'safe_to_delete' => false,
                'reason' => 'Memiliki '.$otherBookingCount.' booking di kamar lain',
            ];
        }

        // 3. Cek apakah memiliki keluhan di kamar lain atau area umum
        $otherKeluhanCount = Keluhan::where('user_id', $user->id)
            ->where(function ($q) use ($kamarId) {
                $q->whereNull('kamar_id')->orWhere('kamar_id', '!=', $kamarId);
            })
            ->count();
        if ($otherKeluhanCount > 0) {
            return [
                'safe_to_delete' => false,
                'reason' => 'Memiliki '.$otherKeluhanCount.' keluhan di luar kamar ini',
            ];
        }

        // 4. Cek rating yang tidak terkait dengan kamar/sewa/keluhan yang akan dihapus
        $otherRatingCount = Rating::where('user_id', $user->id)
            ->where(function ($q) use ($kamarId, $sewaIds, $keluhanIds) {
                $q->where(function ($sub) use ($kamarId) {
                    $sub->whereNull('kamar_id')->orWhere('kamar_id', '!=', $kamarId);
                })->where(function ($sub) use ($sewaIds) {
                    $sub->whereNull('sewa_id')->orWhereNotIn('sewa_id', $sewaIds);
                })->where(function ($sub) use ($keluhanIds) {
                    $sub->whereNull('keluhan_id')->orWhereNotIn('keluhan_id', $keluhanIds);
                });
            })
            ->count();
        if ($otherRatingCount > 0) {
            return [
                'safe_to_delete' => false,
                'reason' => 'Memiliki '.$otherRatingCount.' ulasan di luar kamar ini',
            ];
        }

        // 5. Cek tagihan di luar kamar/sewa yang akan dihapus
        $otherTagihanCount = Tagihan::where('user_id', $user->id)
            ->whereNotIn('id', $tagihanIds)
            ->count();
        if ($otherTagihanCount > 0) {
            return [
                'safe_to_delete' => false,
                'reason' => 'Memiliki '.$otherTagihanCount.' tagihan lain',
            ];
        }

        // 6. Cek perpanjangan di luar sewa yang akan dihapus
        $otherPerpanjanganCount = Perpanjangan::where('user_id', $user->id)
            ->whereNotIn('id', $perpanjanganIds)
            ->count();
        if ($otherPerpanjanganCount > 0) {
            return [
                'safe_to_delete' => false,
                'reason' => 'Memiliki '.$otherPerpanjanganCount.' perpanjangan sewa lain',
            ];
        }

        // 7. Cek pembayaran di luar transaksi kamar yang akan dihapus
        $otherPembayaranCount = Pembayaran::where('user_id', $user->id)
            ->whereNotIn('id', $pembayaranIds)
            ->count();
        if ($otherPembayaranCount > 0) {
            return [
                'safe_to_delete' => false,
                'reason' => 'Memiliki '.$otherPembayaranCount.' pembayaran lain',
            ];
        }

        return [
            'safe_to_delete' => true,
            'reason' => 'Seluruh data pengguna murni hanya terikat pada kamar ini',
        ];
    }

    /**
     * Kumpulkan berkas storage fisik yang aman untuk dihapus.
     *
     * @param  array<int>  $pembayaranIds
     * @param  array<int>  $keluhanIds
     * @param  array<int>  $fotoIds
     * @param  array<string>  $filesToDelete
     */
    protected function collectStorageFiles(
        int $kamarId,
        array $pembayaranIds,
        array $keluhanIds,
        array $fotoIds,
        ?string $kamarCoverFoto,
        array &$filesToDelete
    ): void {
        // Foto cover kamar
        if ($kamarCoverFoto && ! Kamar::where('id', '!=', $kamarId)->where('foto', $kamarCoverFoto)->exists()) {
            $filesToDelete[] = $kamarCoverFoto;
        }

        // Foto galeri kamar
        $galeriFotos = KamarFoto::whereIn('id', $fotoIds)->pluck('foto')->filter()->all();
        foreach ($galeriFotos as $fotoPath) {
            if (! KamarFoto::whereNotIn('id', $fotoIds)->where('foto', $fotoPath)->exists()) {
                $filesToDelete[] = $fotoPath;
            }
        }

        // Bukti pembayaran
        if (! empty($pembayaranIds)) {
            $buktiList = Pembayaran::whereIn('id', $pembayaranIds)->pluck('bukti_pembayaran')->filter()->all();
            foreach ($buktiList as $buktiPath) {
                if (! Pembayaran::whereNotIn('id', $pembayaranIds)->where('bukti_pembayaran', $buktiPath)->exists()) {
                    $filesToDelete[] = $buktiPath;
                }
            }
        }

        // Foto keluhan
        if (! empty($keluhanIds)) {
            $keluhanFotoList = Keluhan::whereIn('id', $keluhanIds)->pluck('foto')->filter()->all();
            foreach ($keluhanFotoList as $fotoPath) {
                if (! Keluhan::whereNotIn('id', $keluhanIds)->where('foto', $fotoPath)->exists()) {
                    $filesToDelete[] = $fotoPath;
                }
            }
        }

        $filesToDelete = array_unique(array_filter($filesToDelete));
    }

    /**
     * Hapus berkas dari storage dengan aman.
     *
     * @param  array<string>  $files
     */
    protected function deleteStorageFiles(array $files): void
    {
        foreach ($files as $filePath) {
            try {
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            } catch (\Throwable $e) {
                Log::warning('KamarCleanupService: Gagal menghapus file storage: '.$e->getMessage(), [
                    'file' => $filePath,
                ]);
            }
        }
    }
}
