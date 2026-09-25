<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePasienRequest;
use App\Models\Pasien;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Manajemen data pasien — view & edit saja (project.md Prompt 3).
 */
class PasienController extends Controller
{
    public function index(Request $request): View
    {
        $keyword = $request->string('q')->toString();

        $pasiens = Pasien::query()
            ->with('user')
            ->when($keyword !== '', fn ($query) => $query->where(function ($query) use ($keyword) {
                $query->where('no_rm', 'like', "%{$keyword}%")
                    ->orWhere('nik', 'like', "%{$keyword}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
            }))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pasien.index', [
            'pasiens' => $pasiens,
            'keyword' => $keyword,
        ]);
    }

    public function edit(Pasien $pasien): View
    {
        $pasien->load('user');

        return view('admin.pasien.edit', ['pasien' => $pasien]);
    }

    public function update(UpdatePasienRequest $request, Pasien $pasien): RedirectResponse
    {
        DB::transaction(function () use ($request, $pasien) {
            $pasien->update($request->safe()->except(['name', 'email', 'phone']));

            if ($pasien->user) {
                $pasien->user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ]);
            }
        });

        return redirect()
            ->route('admin.pasien.index')
            ->with('status', 'Data pasien berhasil diperbarui.');
    }
}
