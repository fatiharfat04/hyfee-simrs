<?php

namespace Database\Factories;

use App\Enums\AntreanStatus;
use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\Poli;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Antrean>
 */
class AntreanFactory extends Factory
{
    protected $model = Antrean::class;

    public function definition(): array
    {
        /*
         * Poli dibuat langsung (eager) supaya kode_antrean bisa dibentuk dari
         * prefix_antrean. nomor_urut memakai unique() per poli-per tanggal,
         * aman dipakai berbarengan (Factory::count() mengevaluasi semua
         * definition sebelum ada yang di-insert).
         */
        $poli = Poli::factory()->create();
        $nomor = fake()->unique('antrean-nomor')->numberBetween(1, 999);

        return [
            'kode_antrean' => sprintf('%s-%03d', $poli->prefix_antrean, $nomor),
            'nomor_urut' => $nomor,
            'pasien_id' => Pasien::factory(),
            'dokter_id' => Dokter::factory()->state(['poli_id' => $poli->id]),
            'poli_id' => $poli->id,
            'jadwal_dokter_id' => null,
            'tanggal_antrean' => today(),
            'jam_daftar' => now()->subMinutes(fake()->numberBetween(5, 60)),
            'jam_dipanggil' => null,
            'jam_selesai' => null,
            'status' => AntreanStatus::MENUNGGU,
            'catatan' => null,
        ];
    }

    public function status(AntreanStatus $status): static
    {
        $sudahDipanggil = in_array($status, [
            AntreanStatus::DIPANGGIL, AntreanStatus::DILAYANI, AntreanStatus::SELESAI,
        ], true);

        return $this->state([
            'status' => $status,
            'jam_dipanggil' => $sudahDipanggil ? now() : null,
            'jam_selesai' => $status === AntreanStatus::SELESAI ? now() : null,
        ]);
    }

    public function untukTanggal(string $tanggal): static
    {
        return $this->state(['tanggal_antrean' => $tanggal]);
    }
}
