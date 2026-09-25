<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',

            // Data diri pasien (project.md Bab 3.3) — wajib di form register
            'nik' => '3201234567890123',
            'tanggal_lahir' => '1995-04-12',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Merdeka No. 1, Jakarta',
            'phone' => '081234567890',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('pasien.dashboard', absolute: false));

        $this->assertDatabaseHas('pasiens', [
            'nik' => '3201234567890123',
            'user_id' => User::where('email', 'test@example.com')->value('id'),
        ]);
    }
}
