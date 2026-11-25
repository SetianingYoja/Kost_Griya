<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\TipeKamar;
use App\Models\Kamar;

class UpdateRoomTypes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'update:room-types {--dry-run : Show changes without applying them}';

    /**
     * The console command description.
     */
    protected $description = 'Update master room types and associated rooms according to business rule changes.';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $this->info('Starting room type update' . ($dryRun ? ' (dry‑run)' : '') . '...');

        // ---------------------------------------------------------------------
        // 1. Load current state for reporting
        // ---------------------------------------------------------------------
        $tipeBefore = TipeKamar::all(['id', 'slug', 'nama_tipe', 'harga_bulanan'])->toArray();
        $this->table(['ID', 'Slug', 'Nama', 'Harga'], $tipeBefore);

        $roomNumbers = [
            'Kamar 101', 'kamar 104', 'kamar 105', 'kamar 106', 'kamar 107', 'kamar 108',
            'Kamar 201', 'Kamar 202', 'Kamar 203',
        ];
        $kamarBefore = Kamar::whereIn('nomor_kamar', $roomNumbers)
            ->get(['id', 'nomor_kamar', 'tipe_kamar_id', 'harga'])
            ->toArray();
        $this->info('Current relevant Kamar records:');
        $this->table(['ID', 'Nomor', 'TipeID', 'Harga'], $kamarBefore);

        // ---------------------------------------------------------------------
        // 2. Resolve target master records (keep existing IDs)
        // ---------------------------------------------------------------------
        $ekonomis = TipeKamar::where('slug', 'standar')->first(); // will become Ekonomis
        $standar = TipeKamar::where('slug', 'deluxe')->first();   // will become Standar

        if (! $ekonomis || ! $standar) {
            $this->error('Required master tipe_kamar records not found (slug "standar" and "deluxe").');
            return 1;
        }

        // ---------------------------------------------------------------------
        // 3. Validate that all nine rooms exist before any change
        // ---------------------------------------------------------------------
        $existingCount = Kamar::whereIn('nomor_kamar', $roomNumbers)->count();
        if ($existingCount !== count($roomNumbers)) {
            $this->error("Expected " . count($roomNumbers) . " rooms but found {$existingCount}. Aborting to avoid partial updates.");
            return 1;
        }

        // ---------------------------------------------------------------------
        // 4. Begin transaction – we will commit only after all validations pass
        // ---------------------------------------------------------------------
        DB::beginTransaction();
        try {
            // ---- Update master tipe_kamar records ----
            $masterUpdates = [
                [
                    'id' => $ekonomis->id,
                    'slug' => 'ekonomis',
                    'nama_tipe' => 'Ekonomis',
                    'harga_bulanan' => 500000,
                ],
                [
                    'id' => $standar->id,
                    'slug' => 'standar',
                    'nama_tipe' => 'Standar',
                    'harga_bulanan' => 800000,
                ],
            ];
            foreach ($masterUpdates as $data) {
                if ($dryRun) {
                    $this->info("Would update TipeKamar ID {$data['id']}: " . json_encode($data));
                } else {
                    TipeKamar::where('id', $data['id'])->update([
                        'slug' => $data['slug'],
                        'nama_tipe' => $data['nama_tipe'],
                        'harga_bulanan' => $data['harga_bulanan'],
                    ]);
                }
            }

            // ---- Update Kamar rows ----
            $ekonomisRoomNumbers = ['Kamar 101', 'kamar 104', 'kamar 105', 'kamar 106', 'kamar 107', 'kamar 108'];
            $standarRoomNumbers = ['Kamar 201', 'Kamar 202', 'Kamar 203'];

            $updateRoomBatch = function (array $numbers, int $tipeId, int $harga) use ($dryRun) {
                $query = Kamar::whereIn('nomor_kamar', $numbers);
                if ($dryRun) {
                    $this->info('Would update rooms: ' . implode(', ', $numbers) . " to tipe_id {$tipeId} and harga {$harga}");
                } else {
                    $query->update([
                        'tipe_kamar_id' => $tipeId,
                        'harga' => $harga,
                    ]);
                }
            };

            $updateRoomBatch($ekonomisRoomNumbers, $ekonomis->id, 500000);
            $updateRoomBatch($standarRoomNumbers, $standar->id, 800000);

            // -----------------------------------------------------------------
            // 5. Commit or rollback based on dry‑run flag
            // -----------------------------------------------------------------
            if ($dryRun) {
                DB::rollBack();
                $this->info('Dry‑run completed – no changes were persisted.');
            } else {
                DB::commit();
                // Show updated data
                $this->info('Updated TipeKamar records:');
                $tipeAfter = TipeKamar::whereIn('id', [$ekonomis->id, $standar->id])
                    ->get(['id', 'slug', 'nama_tipe', 'harga_bulanan'])
                    ->toArray();
                $this->table(['ID', 'Slug', 'Nama', 'Harga'], $tipeAfter);

                $this->info('Updated Kamar records:');
                $kamarAfter = Kamar::whereIn('nomor_kamar', $roomNumbers)
                    ->get(['id', 'nomor_kamar', 'tipe_kamar_id', 'harga'])
                    ->toArray();
                $this->table(['ID', 'Nomor', 'TipeID', 'Harga'], $kamarAfter);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during update: ' . $e->getMessage());
            return 1;
        }

        $this->info('Room type update finished' . ($dryRun ? ' (dry‑run, no changes saved)' : '') . '.');
        return 0;
    }
}
