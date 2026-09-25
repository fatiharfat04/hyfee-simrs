<?php

namespace App\Http\Controllers\Pasien;

use App\Enums\AntreanStatus;
use App\Enums\Hari;
use App\Exceptions\AntreanException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pasien\StoreAntreanRequest;
use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\Poli;
use App\Services\QueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class AntreanController extends Controller
{
    public function __construct(private readonly QueueService $queueService) {}

    /**
     * Form pendaftaran: pilih poli lalu dokter yang punya jadwal hari ini.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->pasien) {
            return redirect()
                ->route('pasien.profil.edit')
                ->with('error', 'Lengkapi data diri Anda terlebih dahulu sebelum mengambil antrean.');
        }

        $hari = Hari::today()->value;

        $dokters = Dokter::query()
            ->where('is_active', true)
            ->whereHas('poli', fn ($query) => $query->where('is_active', true))
            ->whereHas('jadwalDokters', fn ($query) => $query->where('hari', $hari)->where('is_active', true))
            ->with([
                'poli',
                'jadwalDokters' => fn ($query) => $query->where('hari', $hari)->where('is_active', true)->orderBy('jam_mulai'),
            ])
            ->orderBy('poli_id')
            ->get();

        // Sisa kuota per sesi jadwal hari ini
        $terpakai = Antrean::query()
            ->whereIn('jadwal_dokter_id', $dokters->pluck('jadwalDokters.*.id')->flatten())
            ->whereDate('tanggal_antrean', today())
            ->where('status', '!=', AntreanStatus::BATAL->value)
            ->groupBy('jadwal_dokter_id')
            ->selectRaw('jadwal_dokter_id, COUNT(*) as jumlah')
            ->pluck('jumlah', 'jadwal_dokter_id');

        return view('pasien.antrean.create', [
            'dokters' => $dokters,
            'polis' => Poli::query()->where('is_active', true)->orderBy('nama_poli')->get(),
            'terpakai' => $terpakai,
            'antreanAktif' => $user->pasien
                ? $user->pasien->antreans()->whereIn('status', array_map(fn ($s) => $s->value, AntreanStatus::aktif()))
                    ->whereDate('tanggal_antrean', today())->first()
                : null,
        ]);
    }

    /**
     * Simpan pendaftaran — seluruh logika ada di QueueService.
     */
    public function store(StoreAntreanRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->pasien) {
            return redirect()
                ->route('pasien.profil.edit')
                ->with('error', 'Lengkapi data diri Anda terlebih dahulu.');
        }

        $poli = Poli::findOrFail($request->poli_id);
        $dokter = Dokter::findOrFail($request->dokter_id);

        try {
            $antrean = $this->queueService->daftarAntrean(
                pasien: $user->pasien,
                dokter: $dokter,
                poli: $poli,
                tanggal: today()->toDateString(),
            );
        } catch (AntreanException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('pasien.antrean.mine')
            ->with('status', "Pendaftaran berhasil. Nomor antrean Anda {$antrean->kode_antrean}.");
    }

    /**
     * Halaman "Antrean Saya".
     */
    public function mine(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user->pasien) {
            return redirect()
                ->route('pasien.profil.edit')
                ->with('error', 'Lengkapi data diri Anda terlebih dahulu.');
        }

        $antreans = Antrean::query()
            ->with(['poli', 'dokter.user', 'jadwalDokter'])
            ->where('pasien_id', $user->pasien->id)
            ->orderByDesc('tanggal_antrean')
            ->orderByDesc('jam_daftar')
            ->paginate(10)
            ->withQueryString();

        /** @var LengthAwarePaginator $antreans */
        return view('pasien.antrean.mine', [
            'antreans' => $antreans,
            'antreanHariIni' => $antreans->getCollection()
                ->first(fn (Antrean $antrean) => $antrean->tanggal_antrean->isToday()),
        ]);
    }

    /**
     * Batalkan antrean sendiri (hanya selama status masih menunggu).
     */
    public function cancel(Request $request, Antrean $antrean): RedirectResponse
    {
        $this->authorize('cancel', $antrean);

        try {
            $this->queueService->updateStatus($antrean, AntreanStatus::BATAL);
        } catch (AntreanException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('status', "Antrean {$antrean->kode_antrean} berhasil dibatalkan.");
    }
}
