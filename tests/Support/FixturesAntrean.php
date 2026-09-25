<?php

namespace Tests\Support;

use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Pasien;
use App\Models\Poli;
use App\Services\QueueService;

/**
 * Fixture bersama untuk uji antrean (project.md Bab 8).
 *
 * Poli/dokter dibuat dengan atribut deterministik supaya tidak bentrok dengan
 * `fake()->unique()` saat seluruh suite berjalan dalam satu proses.
 */
trait FixturesAntrean
{
    /**
     * Poli + dokter + jadwal aktif. Role `dokter` ikut terpasang.
     *
     * @param  array{kuota?: int}  $opsi
     * @return array{poli: Poli, dokter: Dokter, jadwal: JadwalDokter}
     */
    protected function buatPoliDanDokter(array $opsi = []): array
    {
        $urut = Poli::query()->count() + 1;

        $poli = Poli::factory()->create([
            'kode_poli' => sprintf('POL-%02d', $urut),
            'nama_poli' => 'Poli Uji '.$urut,
            'prefix_antrean' => chr(64 + ((($urut - 1) % 26) + 1)),
        ]);

        $dokter = Dokter::factory()->create([
            'poli_id' => $poli->id,
            'is_active' => true,
        ]);
        $dokter->user->assignRole('dokter');

        $jadwal = JadwalDokter::factory()->create([
            'dokter_id' => $dokter->id,
            'kuota' => $opsi['kuota'] ?? 30,
            'is_active' => true,
        ]);

        return [$poli, $dokter, $jadwal];
    }

    /**
     * Daftar antrean lewat QueueService — jalur yang sama dengan HTTP.
     *
     * @throws \App\Exceptions\AntreanException
     */
    protected function daftarkan(
        Pasien $pasien,
        Dokter $dokter,
        Poli $poli,
        JadwalDokter $jadwal,
        string $tanggal,
    ): Antrean {
        return app(QueueService::class)
            ->daftarAntrean($pasien, $dokter, $poli, $tanggal, $jadwal);
    }
}
