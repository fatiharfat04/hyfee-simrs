<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed 3 role sesuai project.md Bab 3.2.
     */
    public function run(): void
    {
        foreach (['admin', 'dokter', 'pasien'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }
}
