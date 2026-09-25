<?php

namespace App\Console\Commands;

use App\Services\QueueService;
use Illuminate\Console\Command;

/**
 * Cleanup/arsip antrean lama (project.md Bab 5.1 — OPSIONAL).
 *
 * Penomoran sudah di-scope per tanggal_antrean, jadi command ini murni
 * pembersihan. Dijadwalkan tiap tengah malam di routes/console.php.
 */
class ResetHarian extends Command
{
    protected $signature = 'queue:reset-harian';

    protected $description = 'Hapus antrean lama yang statusnya sudah final (selesai/batal/tidak_hadir)';

    public function handle(QueueService $queueService): int
    {
        $dihapus = $queueService->resetHarian();
        $hari = (int) config('queue.reset_days', 30);

        $this->info("Selesai: {$dihapus} antrean lebih tua dari {$hari} hari dihapus.");

        return self::SUCCESS;
    }
}
