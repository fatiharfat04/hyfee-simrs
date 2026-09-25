<?php

namespace App\Http\Controllers\Pasien;

use App\Enums\AntreanStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $pasien = $user->pasien;

        return view('pasien.dashboard', [
            'user' => $user,
            'pasien' => $pasien,
            'antreanHariIni' => $pasien
                ? $pasien->antreans()
                    ->with(['poli', 'dokter.user'])
                    ->whereDate('tanggal_antrean', today())
                    ->where('status', '!=', AntreanStatus::BATAL->value)
                    ->orderByDesc('jam_daftar')
                    ->first()
                : null,
            'totalAntrean' => $pasien ? $pasien->antreans()->count() : 0,
            'selesai' => $pasien
                ? $pasien->antreans()->where('status', AntreanStatus::SELESAI->value)->count()
                : 0,
        ]);
    }
}
