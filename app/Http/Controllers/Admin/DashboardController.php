<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AntreanStatus;
use App\Http\Controllers\Controller;
use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $hariIni = today();

        return view('admin.dashboard', [
            'user' => $request->user(),
            'poliCount' => Poli::where('is_active', true)->count(),
            'dokterCount' => Dokter::where('is_active', true)->count(),
            'antreanHariIni' => Antrean::whereDate('tanggal_antrean', $hariIni)
                ->where('status', '!=', AntreanStatus::BATAL->value)
                ->count(),
            'selesaiHariIni' => Antrean::whereDate('tanggal_antrean', $hariIni)
                ->where('status', AntreanStatus::SELESAI->value)
                ->count(),
        ]);
    }
}
