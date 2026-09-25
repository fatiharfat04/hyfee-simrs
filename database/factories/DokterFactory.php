<?php

namespace Database\Factories;

use App\Models\Dokter;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dokter>
 */
class DokterFactory extends Factory
{
    protected $model = Dokter::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'poli_id' => Poli::factory(),
            'no_sip' => 'SIP-'.fake()->unique()->numberBetween(100000, 999999),
            'gelar_depan' => fake()->randomElement(['dr.', 'dr.']),
            'gelar_belakang' => fake()->randomElement(['Sp.P', 'Sp.A', 'Sp.KG', 'Sp.B', 'Sp.THT']),
            'is_active' => true,
        ];
    }
}
