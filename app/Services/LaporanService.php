<?php

namespace App\Services;

use App\Models\Antrean;
use App\Models\Poli;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Rekap laporan antrean per poli (project.md Prompt 8).
 *
 * Metrik:
 *  - jumlah pasien per poli (rentang hari / bulan)
 *  - rata-rata waktu tunggu  = jam_dipanggil - jam_daftar
 *  - rata-rata waktu layanan = jam_selesai  - jam_dipanggil
 *
 * Perhitungan ada di service, bukan di controller (project.md Bab 5).
 */
class LaporanService
{
    /**
     * Rentang tanggal laporan sesuai periode.
     *
     * @return array{dari: string, sampai: string}
     */
    public function rentang(string $periode, string $tanggal): array
    {
        $hari = Carbon::parse($tanggal);

        if ($periode === 'bulan') {
            return [
                'dari' => $hari->copy()->startOfMonth()->toDateString(),
                'sampai' => $hari->copy()->endOfMonth()->toDateString(),
            ];
        }

        return [
            'dari' => $hari->toDateString(),
            'sampai' => $hari->toDateString(),
        ];
    }

    /**
     * Rekap seluruh poli untuk suatu rentang tanggal.
     *
     * @return array{
     *     rows: array<int, array<string, mixed>>,
     *     total: array<string, mixed>
     * }
     */
    public function rekap(string $dari, string $sampai): array
    {
        $perPoli = Antrean::query()
            ->select(['id', 'poli_id', 'jam_daftar', 'jam_dipanggil', 'jam_selesai'])
            ->whereBetween('tanggal_antrean', [$dari, $sampai])
            ->get()
            ->groupBy('poli_id');

        $rows = Poli::query()
            ->orderBy('kode_poli')
            ->get()
            ->map(fn (Poli $poli) => $this->baris($poli, $perPoli->get($poli->id, collect())))
            ->all();

        $semua = $perPoli->flatten();

        return [
            'rows' => $rows,
            'total' => [
                'jumlah' => array_sum(array_column($rows, 'jumlah')),
                'rata_tunggu' => $this->formatMenit($this->rataRata(
                    $semua->filter(fn (Antrean $a) => $a->jam_daftar && $a->jam_dipanggil),
                    fn (Antrean $a) => $a->jam_daftar->diffInMinutes($a->jam_dipanggil),
                )),
                'rata_layanan' => $this->formatMenit($this->rataRata(
                    $semua->filter(fn (Antrean $a) => $a->jam_dipanggil && $a->jam_selesai),
                    fn (Antrean $a) => $a->jam_dipanggil->diffInMinutes($a->jam_selesai),
                )),
            ],
        ];
    }

    /**
     * Satu baris rekap untuk sebuah poli (termasuk poli tanpa antrean).
     *
     * @param  Collection<int, Antrean>  $antreans
     * @return array<string, mixed>
     */
    private function baris(Poli $poli, Collection $antreans): array
    {
        $rataTunggu = $this->rataRata(
            $antreans->filter(fn (Antrean $a) => $a->jam_daftar && $a->jam_dipanggil),
            fn (Antrean $a) => $a->jam_daftar->diffInMinutes($a->jam_dipanggil),
        );

        $rataLayanan = $this->rataRata(
            $antreans->filter(fn (Antrean $a) => $a->jam_dipanggil && $a->jam_selesai),
            fn (Antrean $a) => $a->jam_dipanggil->diffInMinutes($a->jam_selesai),
        );

        return [
            'kode_poli' => $poli->kode_poli,
            'nama_poli' => $poli->nama_poli,
            'lokasi_ruang' => $poli->lokasi_ruang,
            'jumlah' => $antreans->count(),
            'rata_tunggu' => $rataTunggu,
            'rata_tunggu_teks' => $this->formatMenit($rataTunggu),
            'rata_layanan' => $rataLayanan,
            'rata_layanan_teks' => $this->formatMenit($rataLayanan),
        ];
    }

    /**
     * @param  Collection<int, Antrean>  $antreans
     */
    private function rataRata(Collection $antreans, callable $hitung): ?float
    {
        if ($antreans->isEmpty()) {
            return null;
        }

        return round((float) $antreans->avg($hitung), 1);
    }

    /**
     * Tampilan menit (koma desimal, sesuai format ID) atau strip bila kosong.
     */
    public function formatMenit(?float $menit): string
    {
        if ($menit === null) {
            return '—';
        }

        return number_format($menit, 1, ',', '.').' menit';
    }
}
