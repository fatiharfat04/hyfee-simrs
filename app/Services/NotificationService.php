<?php

namespace App\Services;

use App\Enums\AntreanStatus;
use App\Models\Antrean;
use App\Notifications\GiliranDipanggilNotification;
use App\Notifications\PeringatanGiliranBerikutNotification;

/**
 * Notifikasi pasien terkait pemanggilan antrean (project.md Bab 5.2).
 *
 * Channel: database (wajib) + broadcast (toast real-time di halaman pasien).
 * Mail/WhatsApp dipending ke fase 2 sesuai spesifikasi.
 */
class NotificationService
{
    /**
     * Dipanggil oleh QueueService setiap kali sebuah nomor berstatus dipanggil.
     *
     * 1. Pemberitahuan giliran → pasien yang baru dipanggil.
     * 2. Peringatan dini       → pasien nomor_urut = (yang dipanggil + 2).
     */
    public function kirimUntukPanggilan(Antrean $dipanggil): void
    {
        $this->kirimPemberitahuanGiliran($dipanggil);
        $this->kirimPeringatanDini($dipanggil);
    }

    /**
     * Pemberitahuan giliran ke pasien yang nomornya baru dipanggil.
     */
    public function kirimPemberitahuanGiliran(Antrean $dipanggil): void
    {
        $pasien = $dipanggil->pasien;

        if (! $pasien?->user) {
            return;
        }

        $pasien->user->notify(new GiliranDipanggilNotification($dipanggil));
    }

    /**
     * Peringatan dini ke pasien dengan nomor_urut = nomor yang dipanggil + 2,
     * untuk dokter & tanggal yang sama.
     */
    public function kirimPeringatanDini(Antrean $dipanggil): void
    {
        $target = Antrean::query()
            ->with(['poli', 'dokter'])
            ->where('dokter_id', $dipanggil->dokter_id)
            ->whereDate('tanggal_antrean', $dipanggil->tanggal_antrean)
            ->where('nomor_urut', $dipanggil->nomor_urut + 2)
            ->where('status', AntreanStatus::MENUNGGU->value)
            ->first();

        $pasien = $target?->pasien;

        if (! $pasien?->user) {
            return;
        }

        $pasien->user->notify(new PeringatanGiliranBerikutNotification($target));
    }
}
