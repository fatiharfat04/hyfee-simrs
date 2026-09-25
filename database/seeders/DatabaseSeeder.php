<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        // Akun demo per role (password: password) — untuk keperluan testing/develop.
        $accounts = [
            ['role' => 'admin', 'name' => 'Administrator', 'email' => 'admin@simrs.test', 'phone' => '08111111111'],
            ['role' => 'dokter', 'name' => 'Andini Pratiwi', 'email' => 'dokter@simrs.test', 'phone' => '08222222222'],
            ['role' => 'pasien', 'name' => 'Budi Santoso', 'email' => 'pasien@simrs.test', 'phone' => '08333333333'],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'phone' => $account['phone'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );

            $user->assignRole($account['role']);
        }

        $this->call([
            MasterDataSeeder::class,
        ]);
    }
}
