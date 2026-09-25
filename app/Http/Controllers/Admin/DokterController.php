<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDokterRequest;
use App\Http\Requests\Admin\UpdateDokterRequest;
use App\Models\Dokter;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class DokterController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->string('q')->toString();
        $poliId = $request->integer('poli_id');

        $dokters = Dokter::query()
            ->with(['user', 'poli'])
            ->when($keyword !== '', fn ($query) => $query->where(function ($query) use ($keyword) {
                $query->where('no_sip', 'like', "%{$keyword}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
            }))
            ->when($poliId > 0, fn ($query) => $query->where('poli_id', $poliId))
            ->orderBy('no_sip')
            ->paginate(10)
            ->withQueryString();

        return view('admin.dokter.index', [
            'dokters' => $dokters,
            'polis' => Poli::orderBy('nama_poli')->get(),
            'keyword' => $keyword,
            'poliTerpilih' => $poliId,
        ]);
    }

    public function create(): View
    {
        return view('admin.dokter.create', [
            'polis' => Poli::where('is_active', true)->orderBy('nama_poli')->get(),
        ]);
    }

    public function store(StoreDokterRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'is_active' => true,
            ]);

            $user->assignRole('dokter');

            Dokter::create([
                'user_id' => $user->id,
                'poli_id' => $request->poli_id,
                'no_sip' => $request->no_sip,
                'gelar_depan' => $request->gelar_depan,
                'gelar_belakang' => $request->gelar_belakang,
                'is_active' => $request->boolean('is_active', true),
            ]);
        });

        return redirect()
            ->route('admin.dokter.index')
            ->with('status', 'Dokter berhasil ditambahkan.');
    }

    public function show(Dokter $dokter): View
    {
        $dokter->load(['user', 'poli', 'jadwalDokters' => fn ($q) => $q->orderBy('hari')->orderBy('jam_mulai')]);

        return view('admin.dokter.show', [
            'dokter' => $dokter,
            'antreanHariIni' => $dokter->antreans()->whereDate('tanggal_antrean', today())->count(),
        ]);
    }

    public function edit(Dokter $dokter): View
    {
        $dokter->load('user');

        return view('admin.dokter.edit', [
            'dokter' => $dokter,
            'polis' => Poli::orderBy('nama_poli')->get(),
        ]);
    }

    public function update(UpdateDokterRequest $request, Dokter $dokter): RedirectResponse
    {
        DB::transaction(function () use ($request, $dokter) {
            $dokter->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            if ($request->filled('password')) {
                $dokter->user->update(['password' => Hash::make($request->password)]);
            }

            $dokter->update([
                'poli_id' => $request->poli_id,
                'no_sip' => $request->no_sip,
                'gelar_depan' => $request->gelar_depan,
                'gelar_belakang' => $request->gelar_belakang,
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return redirect()
            ->route('admin.dokter.index')
            ->with('status', 'Data dokter berhasil diperbarui.');
    }

    public function destroy(Dokter $dokter): RedirectResponse
    {
        if ($dokter->antreans()->exists() || $dokter->jadwalDokters()->exists()) {
            return back()->with('error', 'Dokter tidak dapat dihapus karena masih memiliki jadwal/antrean. Nonaktifkan saja.');
        }

        DB::transaction(function () use ($dokter) {
            $user = $dokter->user;
            $dokter->delete();
            $user?->delete();
        });

        return redirect()
            ->route('admin.dokter.index')
            ->with('status', 'Dokter berhasil dihapus.');
    }
}
