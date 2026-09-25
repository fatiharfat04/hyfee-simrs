<?php

namespace Database\Seeders;

use App\Enums\Hari;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Pasien;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data dummy sesuai project.md Bab Prompt 2:
 * 3 poli, 2 dokter per poli, jadwal praktik seminggu.
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $polis = $this->seedPoli();
        $this->seedDokters($polis);
        $this->seedJadwalDokters();
        $this->seedPasienDemo();
    }

    /** @return array<string, Poli> */
    private function seedPoli(): array
    {
        $data = [
            ['kode_poli' => 'POL-01', 'nama_poli' => 'Poli Umum', 'prefix_antrean' => 'A', 'lokasi_ruang' => 'Lantai 1 Ruang 1'],
            ['kode_poli' => 'POL-02', 'nama_poli' => 'Poli Gigi', 'prefix_antrean' => 'B', 'lokasi_ruang' => 'Lantai 1 Ruang 2'],
            ['kode_poli' => 'POL-03', 'nama_poli' => 'Poli Anak', 'prefix_antrean' => 'C', 'lokasi_ruang' => 'Lantai 2 Ruang 1'],
        ];

        $polis = [];
        foreach ($data as $item) {
            $polis[$item['nama_poli']] = Poli::query()->firstOrCreate(
                ['kode_poli' => $item['kode_poli']],
                $item + ['is_active' => true],
            );
        }

        return $polis;
    }

    /**
     * 2 dokter per poli.
     *
     * @param  array<string, Poli>  $polis
     */
    private function seedDokters(array $polis): void
    {
        $dokters = [
            'Poli Umum' => [
                ['email' => 'dokter@simrs.test', 'name' => 'Andini Pratiwi', 'gelar_depan' => 'dr.', 'gelar_belakang' => 'Sp.P', 'no_sip' => 'SIP-2026-0001'],
                ['email' => 'dokter2@simrs.test', 'name' => 'Bima Santosa', 'gelar_depan' => 'dr.', 'gelar_belakang' => 'Sp.P', 'no_sip' => 'SIP-2026-0002'],
            ],
            'Poli Gigi' => [
                ['email' => 'dokter3@simrs.test', 'name' => 'Citra Lestari', 'gelar_depan' => 'drg.', 'gelar_belakang' => 'Sp.KG', 'no_sip' => 'SIP-2026-0003'],
                ['email' => 'dokter4@simrs.test', 'name' => 'Dimas Prabowo', 'gelar_depan' => 'drg.', 'gelar_belakang' => 'Sp.KG', 'no_sip' => 'SIP-2026-0004'],
            ],
            'Poli Anak' => [
                ['email' => 'dokter5@simrs.test', 'name' => 'Eka Wijaya', 'gelar_depan' => 'dr.', 'gelar_belakang' => 'Sp.A', 'no_sip' => 'SIP-2026-0005'],
                ['email' => 'dokter6@simrs.test', 'name' => 'Fitri Handayani', 'gelar_depan' => 'dr.', 'gelar_belakang' => 'Sp.A', 'no_sip' => 'SIP-2026-0006'],
            ],
        ];

        foreach ($dokters as $namaPoli => $items) {
            foreach ($items as $item) {
                $user = User::query()->firstOrCreate(
                    ['email' => $item['email']],
                    [
                        'name' => $item['name'],
                        'phone' => '08123456'.substr($item['no_sip'], -4),
                        'password' => Hash::make('password'),
                        'is_active' => true,
                    ],
                );
                $user->assignRole('dokter');

                Dokter::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'poli_id' => $polis[$namaPoli]->id,
                        'no_sip' => $item['no_sip'],
                        'gelar_depan' => $item['gelar_depan'],
                        'gelar_belakang' => $item['gelar_belakang'],
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    /** Jadwal praktik seminggu penuh (senin - minggu) untuk semua dokter. */
    private function seedJadwalDokters(): void
    {
        Dokter::query()->with('jadwalDokters')->each(function (Dokter $dokter): void {
            $jamMulai = $dokter->id % 2 === 0 ? '08:00' : '14:00';

            foreach (Hari::cases() as $hari) {
                JadwalDokter::query()->firstOrCreate(
                    [
                        'dokter_id' => $dokter->id,
                        'hari' => $hari->value,
                        'jam_mulai' => $jamMulai,
                    ],
                    [
                        'jam_selesai' => sprintf('%02d:00', ((int) substr($jamMulai, 0, 2)) + 6),
                        'kuota' => 30,
                        'is_active' => true,
                    ],
                );
            }
        });
    }

    /** Data pasien untuk akun demo. */
    private function seedPasienDemo(): void
    {
        $user = User::query()->where('email', 'pasien@simrs.test')->first();

        if (! $user || $user->pasien()->exists()) {
            return;
        }

        Pasien::create([
            'user_id' => $user->id,
            'nik' => '3201234567890001',
            'tanggal_lahir' => '1990-05-12',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Merdeka No. 10, Jakarta Pusat',
            'no_telp' => '08333333333',
            'golongan_darah' => 'O',
        ]);
    }
}
