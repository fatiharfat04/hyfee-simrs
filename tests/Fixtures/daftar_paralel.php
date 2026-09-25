<?php

/**
 * Fixture uji race condition (project.md Bab 8 checklist #3).
 *
 * Menjalankan pendaftaran antrean di proses PHP terpisah supaya dua proses
 * benar-benar berjalan paralel terhadap database yang sama — sesuai skenario
 * "2 request bersamaan".
 *
 * Dipakai oleh Tests\Feature\Antrean\RaceConditionTest.
 *
 * php tests/Fixtures/daftar_paralel.php <dokter_id> <poli_id> <tanggal> <id1,id2,...>
 */

use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\Poli;
use App\Services\QueueService;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$dokter = Dokter::query()->findOrFail((int) ($argv[1] ?? 0));
$poli = Poli::query()->findOrFail((int) ($argv[2] ?? 0));
$tanggal = (string) ($argv[3] ?? '');
$jadwal = $dokter->jadwalDokters()->where('is_active', true)->firstOrFail();

$service = app(QueueService::class);
$hasil = [];

foreach (array_filter(explode(',', (string) ($argv[4] ?? ''))) as $id) {
    try {
        $antrean = $service->daftarAntrean(
            Pasien::query()->findOrFail((int) $id),
            $dokter,
            $poli,
            $tanggal,
            $jadwal,
        );

        $hasil[] = ['nomor' => $antrean->nomor_urut, 'kode' => $antrean->kode_antrean];
    } catch (Throwable $exception) {
        $hasil[] = ['gagal' => $exception->getMessage()];
    }
}

// Penanda supaya output JSON tetap terbaca walau ada catatan tambahan.
echo '###HASIL###'.json_encode($hasil).PHP_EOL;
