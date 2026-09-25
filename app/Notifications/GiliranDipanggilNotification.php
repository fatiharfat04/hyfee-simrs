<?php

namespace App\Notifications;

use App\Models\Antrean;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Pemberitahuan giliran: nomor antrean pasien baru saja dipanggil
 * (project.md Bab 5.2 & Prompt 7).
 *
 * Channel: database (wajib, untuk lonceng) + broadcast (toast real-time
 * di halaman "Antrean Saya").
 */
class GiliranDipanggilNotification extends Notification
{
    use Queueable, SerializesModels;

    public function __construct(public Antrean $antrean) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Payload untuk channel database sekaligus broadcast.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->antrean->loadMissing(['poli', 'dokter']);

        return [
            'tipe' => 'giliran',
            'judul' => 'Giliran Anda Sudah Dipanggil',
            'pesan' => sprintf(
                'Nomor %s dipanggil sekarang. Silakan menuju %s (%s).',
                $this->antrean->kode_antrean,
                $this->antrean->poli?->nama_poli,
                $this->antrean->poli?->lokasi_ruang,
            ),
            'antrean_id' => $this->antrean->id,
            'kode_antrean' => $this->antrean->kode_antrean,
            'nomor_urut' => $this->antrean->nomor_urut,
            'poli' => $this->antrean->poli?->nama_poli,
            'ruang' => $this->antrean->poli?->lokasi_ruang,
            'dokter' => $this->antrean->dokter?->namaLengkap(),
            'status' => $this->antrean->status->value,
        ];
    }
}
