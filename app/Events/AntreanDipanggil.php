<?php

namespace App\Events;

use App\Models\Antrean;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dipublikasikan saat nomor antrean dipanggil — dipakai oleh
 * papan antrean publik & toast halaman pasien (project.md Bab 5.1/6).
 */
class AntreanDipanggil implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Antrean $antrean) {}

    /**
     * Channel publik, tanpa autentikasi — papan antrean dibuka di TV.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('papan-antrean')];
    }

    public function broadcastWith(): array
    {
        $this->antrean->loadMissing(['poli', 'dokter.user', 'pasien.user']);

        return [
            'antrean_id' => $this->antrean->id,
            'kode_antrean' => $this->antrean->kode_antrean,
            'nomor_urut' => $this->antrean->nomor_urut,
            'status' => $this->antrean->status->value,
            'poli' => [
                'id' => $this->antrean->poli?->id,
                'nama_poli' => $this->antrean->poli?->nama_poli,
                'lokasi_ruang' => $this->antrean->poli?->lokasi_ruang,
            ],
            'dokter' => $this->antrean->dokter?->namaLengkap(),
            'waktu' => $this->antrean->jam_dipanggil?->toIso8601String(),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AntreanDipanggil';
    }
}
