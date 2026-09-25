<?php

namespace Database\Factories;

use App\Enums\Hari;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JadwalDokter>
 */
class JadwalDokterFactory extends Factory
{
    protected $model = JadwalDokter::class;

    public function definition(): array
    {
        $jamMulai = fake()->randomElement(['08:00', '09:00', '13:00', '14:00']);
        $jamSelesai = sprintf('%02d:00', ((int) substr($jamMulai, 0, 2)) + 4);

        return [
            'dokter_id' => Dokter::factory(),
            'hari' => fake()->randomElement(Hari::cases()),
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'kuota' => 30,
            'is_active' => true,
        ];
    }
}
