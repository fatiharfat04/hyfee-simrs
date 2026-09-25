<?php

namespace Database\Factories;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use App\Models\Pasien;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pasien>
 */
class PasienFactory extends Factory
{
    protected $model = Pasien::class;

    public function definition(): array
    {
        return [
            // no_rm dikosongkan -> di-auto-generate oleh model (RM-YYYYMM-0001)
            'user_id' => User::factory(),
            'nik' => (string) fake()->unique()->numberBetween(1000000000000000, 9999999999999999),
            'tanggal_lahir' => fake()->dateTimeBetween('-70 years', '-5 years')->format('Y-m-d'),
            'jenis_kelamin' => fake()->randomElement(JenisKelamin::cases()),
            'alamat' => fake()->address(),
            'no_telp' => '08'.fake()->numerify('##########'),
            'golongan_darah' => fake()->randomElement(array_merge([null], GolonganDarah::cases())),
        ];
    }

    /**
     * Pasien walk-in tanpa akun (user_id nullable, project.md Bab 3.3).
     */
    public function tanpaAkun(): static
    {
        return $this->state(['user_id' => null]);
    }
}
