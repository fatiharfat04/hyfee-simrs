<?php

namespace App\Http\Controllers\Admin;

use App\Exports\LaporanAntreanExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LaporanRequest;
use App\Services\LaporanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Laporan & statistik antrean (project.md Prompt 8).
 * Perhitungan ada di LaporanService, controller hanya merapatkan data.
 */
class LaporanController extends Controller
{
    public function __construct(private readonly LaporanService $laporanService) {}

    /**
     * Halaman laporan: jumlah pasien, waktu tunggu & waktu layanan per poli.
     */
    public function index(LaporanRequest $request): Response|View
    {
        return view('admin.laporan.index', $this->siapkan($request));
    }

    /**
     * Ekspor laporan ke Excel (Laravel Excel) atau PDF (DomPDF).
     */
    public function export(LaporanRequest $request): Response
    {
        $data = $this->siapkan($request);
        $format = $request->input('format', 'excel');
        $nama = sprintf('laporan-antrean-%s-%s', $data['dari'], $data['sampai']);

        if ($format === 'pdf') {
            return Pdf::loadView('admin.laporan.pdf', $data)
                ->setPaper('a4', 'landscape')
                ->download($nama.'.pdf');
        }

        return Excel::download(new LaporanAntreanExport($data), $nama.'.xlsx');
    }

    /**
     * Gabungkan filter + hasil rekap untuk view maupun file ekspor.
     *
     * @return array<string, mixed>
     */
    private function siapkan(LaporanRequest $request): array
    {
        $periode = $request->string('periode')->toString() ?: 'hari';
        $tanggal = $request->string('tanggal')->toString() ?: today()->toDateString();

        $rentang = $this->laporanService->rentang($periode, $tanggal);
        $rekap = $this->laporanService->rekap($rentang['dari'], $rentang['sampai']);

        $acuan = Carbon::parse($tanggal);

        return [
            'periode' => $periode,
            'tanggal' => $tanggal,
            'dari' => $rentang['dari'],
            'sampai' => $rentang['sampai'],
            'labelPeriode' => $periode === 'bulan'
                ? $acuan->translatedFormat('F Y')
                : $acuan->translatedFormat('l, d F Y'),
            'labelRentang' => $rentang['dari'] === $rentang['sampai']
                ? Carbon::parse($rentang['dari'])->translatedFormat('d F Y')
                : Carbon::parse($rentang['dari'])->translatedFormat('d F Y')
                    .' – '
                    .Carbon::parse($rentang['sampai'])->translatedFormat('d F Y'),
            'rows' => $rekap['rows'],
            'total' => $rekap['total'],
        ];
    }
}
