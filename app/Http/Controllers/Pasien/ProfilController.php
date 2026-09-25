<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pasien\StoreProfilRequest;
use App\Models\Pasien;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lengkapi / ubah data diri pasien (rekap medis) oleh pemilik akun.
 */
class ProfilController extends Controller
{
    public function edit(Request $request): View
    {
        return view('pasien.profil.edit', [
            'pasien' => $request->user()->pasien,
        ]);
    }

    public function update(StoreProfilRequest $request): RedirectResponse
    {
        $user = $request->user();

        $pasien = $user->pasien ?: new Pasien(['user_id' => $user->id]);

        $pasien->fill($request->safe()->all());

        if (! $pasien->user_id) {
            $pasien->user_id = $user->id;
        }

        $pasien->save();

        return redirect()
            ->route('pasien.dashboard')
            ->with('status', 'Data diri berhasil disimpan.');
    }
}
