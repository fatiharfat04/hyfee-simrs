<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreJadwalRequest;
use App\Http\Requests\Admin\UpdateJadwalRequest;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalDokterController extends Controller
{
    public function index(Request $request): View
    {
        $dokterId = $request->integer('dokter_id');

        $jadwals = JadwalDokter::query()
            ->with(['dokter.user', 'dokter.poli'])
            ->when($dokterId > 0, fn ($query) => $query->where('dokter_id', $dokterId))
            ->orderByRaw("FIELD(hari, 'senin','selasa','rabu','kamis','jumat','sabtu','minggu')")
            ->orderBy('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        return view('admin.jadwal.index', [
            'jadwals' => $jadwals,
            'dokters' => Dokter::with('user')->orderBy('no_sip')->get(),
            'dokterTerpilih' => $dokterId,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.jadwal.create', [
            'dokters' => Dokter::with('user')->where('is_active', true)->orderBy('no_sip')->get(),
            'dokterTerpilih' => $request->integer('dokter_id'),
        ]);
    }

    public function store(StoreJadwalRequest $request): RedirectResponse
    {
        JadwalDokter::create([
            'dokter_id' => $request->dokter_id,
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'kuota' => $request->kuota,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.jadwal.index')
            ->with('status', 'Jadwal praktik berhasil ditambahkan.');
    }

    public function edit(JadwalDokter $jadwal): View
    {
        return view('admin.jadwal.edit', [
            'jadwal' => $jadwal,
            'dokters' => Dokter::with('user')->orderBy('no_sip')->get(),
        ]);
    }

    public function update(UpdateJadwalRequest $request, JadwalDokter $jadwal): RedirectResponse
    {
        $jadwal->update([
            'dokter_id' => $request->dokter_id,
            'hari' => $request->hari,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'kuota' => $request->kuota,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.jadwal.index')
            ->with('status', 'Jadwal praktik berhasil diperbarui.');
    }

    public function destroy(JadwalDokter $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return redirect()
            ->route('admin.jadwal.index')
            ->with('status', 'Jadwal praktik berhasil dihapus.');
    }
}
