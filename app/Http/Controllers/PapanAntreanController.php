<?php

namespace App\Http\Controllers;

use App\Enums\AntreanStatus;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Papan antrean publik (project.md Bab 6 & Prompt 6).
 * Tanpa autentikasi — dipasang di TV/monitor rumah sakit.
 */
class PapanAntreanController extends Controller
{
    public function show(Request $request, Poli $poli): View
    {
        $dasar = fn () => $poli->antreans()
            ->with(['dokter.user'])
            ->whereDate('tanggal_antrean', today());

        // Nomor yang sedang dipanggil (terbaru lebih dulu ditampilkan).
        $dipanggil = $dasar()
            ->whereIn('status', [AntreanStatus::DIPANGGIL->value, AntreanStatus::DILAYANI->value])
            ->orderByDesc('jam_dipanggil')
            ->orderByDesc('nomor_urut')
            ->first();

        // Sisa antrean menunggu — dikirim ke client sekali, lalu dikurangi
        // oleh event broadcast (tanpa polling).
        $menunggu = $dasar()
            ->where('status', AntreanStatus::MENUNGGU->value)
            ->orderBy('nomor_urut')
            ->pluck('kode_antrean')
            ->all();

        // Nomor yang sudah terpanggil hari ini (untuk daftar "terakhir").
        $sudahDipanggil = $dasar()
            ->whereNotNull('jam_dipanggil')
            ->orderByDesc('jam_dipanggil')
            ->pluck('kode_antrean')
            ->reverse()
            ->values()
            ->all();

        return view('papan-antrean.show', [
            'poli' => $poli,
            'dipanggil' => $dipanggil,
            'menunggu' => $menunggu,
            'sudahDipanggil' => array_values(array_diff($sudahDipanggil, [$dipanggil?->kode_antrean])),
            'dokterPraktik' => $poli->dokters()
                ->where('is_active', true)
                ->whereHas('jadwalDokters', function ($query) {
                    $query->where('hari', \App\Enums\Hari::today()->value)
                        ->where('is_active', true);
                })
                ->with('user')
                ->get(),
            'rekap' => [
                'menunggu' => $dasar()->where('status', AntreanStatus::MENUNGGU->value)->count(),
                'selesai' => $dasar()->where('status', AntreanStatus::SELESAI->value)->count(),
            ],
        ]);
    }
}
