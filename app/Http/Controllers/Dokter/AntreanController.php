<?php

namespace App\Http\Controllers\Dokter;

use App\Enums\AntreanStatus;
use App\Exceptions\AntreanException;
use App\Http\Controllers\Controller;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Aksi kritis dokter terhadap antreannya sendiri (project.md Bab 6 & 5.3).
 */
class AntreanController extends Controller
{
    public function __construct(private readonly QueueService $queueService) {}

    /**
     * Panggil nomor menunggu terkecil milik dokter ini.
     * Akses divalidasi lewat AntreanPolicy::call — dokter hanya boleh
     * mengubah antrean miliknya sendiri.
     */
    public function panggil(Request $request, \App\Models\Antrean $antrean): RedirectResponse
    {
        $this->authorize('call', $antrean);

        if ($antrean->status !== AntreanStatus::MENUNGGU) {
            return back()->with('error', 'Antrean tersebut sudah tidak berstatus menunggu.');
        }

        try {
            $dipanggil = $this->queueService->panggilBerikutnya(
                $request->user()->dokter,
                $antrean->tanggal_antrean->toDateString(),
            );
        } catch (AntreanException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        if (! $dipanggil) {
            return back()->with('error', 'Tidak ada antrean menunggu saat ini.');
        }

        return back()->with('status', "Memanggil nomor {$dipanggil->kode_antrean}.");
    }

    /**
     * Selesaikan antrean yang sedang dilayani (transisi mengikuti state machine Bab 4:
     * dipanggil -> dilayani -> selesai, tidak pernah melompat).
     */
    public function selesai(Request $request, \App\Models\Antrean $antrean): RedirectResponse
    {
        $this->authorize('call', $antrean);

        try {
            if ($antrean->status === AntreanStatus::DIPANGGIL) {
                $this->queueService->updateStatus($antrean, AntreanStatus::DILAYANI);
            }

            $this->queueService->updateStatus($antrean, AntreanStatus::SELESAI);
        } catch (AntreanException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', "Antrean {$antrean->kode_antrean} selesai dilayani.");
    }
}
