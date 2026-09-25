<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Notifikasi antrean milik pasien (project.md Bab 5.2, channel database).
 * Selalu di-scope ke user login — notifikasi milik orang lain 404.
 */
class NotifikasiController extends Controller
{
    /**
     * Tandai satu notifikasi sudah dibaca (diklik dari lonceng).
     */
    public function baca(Request $request, string $notifikasi): RedirectResponse
    {
        $row = $request->user()
            ->notifications()
            ->whereKey($notifikasi)
            ->firstOrFail();

        $row->markAsRead();

        return back();
    }

    /**
     * Tandai seluruh notifikasi belum dibaca sudah dibaca.
     */
    public function bacaSemua(Request $request): RedirectResponse
    {
        foreach ($request->user()->unreadNotifications as $row) {
            $row->markAsRead();
        }

        return back();
    }
}
