<?php

namespace App\Policies;

use App\Enums\AntreanStatus;
use App\Models\Antrean;
use App\Models\User;

/**
 * Otorisasi per-record (project.md Bab 5.3) — middleware role saja tidak cukup.
 */
class AntreanPolicy
{
    /**
     * Hanya dokter pemilik antrean yang boleh memanggil/mengubah statusnya.
     */
    public function call(User $user, Antrean $antrean): bool
    {
        return $user->dokter !== null
            && $user->dokter->id === $antrean->dokter_id;
    }

    /**
     * Batal: admin, atau pasien pemilik antrean selama statusnya masih menunggu.
     */
    public function cancel(User $user, Antrean $antrean): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->pasien !== null
            && $user->pasien->id === $antrean->pasien_id
            && $antrean->status === AntreanStatus::MENUNGGU;
    }
}
