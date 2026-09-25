<?php

namespace App\Notifications;

use App\Models\Antrean;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

/**
 * Peringatan dini: pasien dengan nomor_urut = nomor yang baru dipanggil + 2
 * diberi tahu bahwa gilirannya hampir tiba (project.md Bab 5.2 & Prompt 7).
 *
 * Channel: database (wajib, untuk lonceng) + broadcast (toast real-time
 * di halaman "Antrean Saya").
 */
class PeringatanGiliranBerikutNotification extends Notification
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
            'tipe' => 'peringatan',
            'judul' => 'Giliran Anda Segera Tiba',
            'pesan' => sprintf(
                'Nomor Anda %s. Harap bersiap di %s (%s) — tersisa 2 giliran.',
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
