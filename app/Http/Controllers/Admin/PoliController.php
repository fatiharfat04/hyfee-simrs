<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePoliRequest;
use App\Http\Requests\Admin\UpdatePoliRequest;
use App\Models\Poli;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PoliController extends Controller
{
    /**
     * Daftar poli.
     */
    public function index(Request $request): View
    {
        $keyword = $request->string('q')->toString();

        $polis = Poli::query()
            ->when($keyword !== '', fn ($query) => $query->where(function ($query) use ($keyword) {
                $query->where('nama_poli', 'like', "%{$keyword}%")
                    ->orWhere('kode_poli', 'like', "%{$keyword}%");
            }))
            ->orderBy('kode_poli')
            ->paginate(10)
            ->withQueryString();

        return view('admin.poli.index', [
            'polis' => $polis,
            'keyword' => $keyword,
        ]);
    }

    public function create(): View
    {
        return view('admin.poli.create');
    }

    public function store(StorePoliRequest $request): RedirectResponse
    {
        Poli::create($request->safe()->except('is_active') + [
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.poli.index')
            ->with('status', 'Poli berhasil ditambahkan.');
    }

    public function show(Poli $poli): View
    {
        $poli->load(['dokters.user', 'antreans' => fn ($query) => $query->latest('tanggal_antrean')->limit(20)]);

        return view('admin.poli.show', ['poli' => $poli]);
    }

    public function edit(Poli $poli): View
    {
        return view('admin.poli.edit', ['poli' => $poli]);
    }

    public function update(UpdatePoliRequest $request, Poli $poli): RedirectResponse
    {
        $poli->update($request->safe()->except('is_active') + [
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.poli.index')
            ->with('status', 'Poli berhasil diperbarui.');
    }

    public function destroy(Poli $poli): RedirectResponse
    {
        if ($poli->dokters()->exists() || $poli->antreans()->exists()) {
            return back()->with('error', 'Poli tidak dapat dihapus karena masih terkait dokter/antrean.');
        }

        $poli->delete();

        return redirect()
            ->route('admin.poli.index')
            ->with('status', 'Poli berhasil dihapus.');
    }
}
