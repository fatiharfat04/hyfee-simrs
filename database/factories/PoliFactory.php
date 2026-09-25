<?php

namespace Database\Factories;

use App\Models\Poli;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Poli>
 */
class PoliFactory extends Factory
{
    protected $model = Poli::class;

    public function definition(): array
    {
        $kode = fake()->unique()->numberBetween(1, 99);

        return [
            'kode_poli' => 'POL-'.str_pad((string) $kode, 2, '0', STR_PAD_LEFT),
            'nama_poli' => fake()->unique()->randomElement([
                'Poli Umum', 'Poli Gigi', 'Poli Anak', 'Poli Kandungan',
                'Poli Penyakit Dalam', 'Poli THT', 'Poli Mata', 'Poli Kulit',
            ]),
            'prefix_antrean' => fake()->unique()->randomElement(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']),
            'lokasi_ruang' => 'Lantai '.fake()->numberBetween(1, 4).' Ruang '.fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(['is_active' => false]);
    }
}
