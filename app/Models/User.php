<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function pasien(): HasOne
    {
        return $this->hasOne(Pasien::class);
    }

    public function dokter(): HasOne
    {
        return $this->hasOne(Dokter::class);
    }

    /**
     * Role utama user (kolom role_id adalah mirror dari role spatie).
     */
    public function role(): ?Role
    {
        return $this->belongsTo(Role::class, 'role_id')->first();
    }

    /**
     * Nama role utama, contoh: 'admin'.
     */
    public function roleName(): ?string
    {
        return $this->role()?->name ?? $this->getRoleNames()->first();
    }

    /**
     * Route dashboard sesuai role (project.md Bab 1 & Bab 6).
     */
    public function dashboardRoute(): string
    {
        return match ($this->roleName()) {
            'admin' => route('admin.dashboard', absolute: false),
            'dokter' => route('dokter.dashboard', absolute: false),
            'pasien' => route('pasien.dashboard', absolute: false),
            default => '/',
        };
    }

    /**
     * Prefix route sesuai role, contoh: 'admin.' — dipakai untuk navigasi.
     */
    public function routePrefix(): string
    {
        $role = $this->roleName();

        return in_array($role, ['admin', 'dokter', 'pasien'], true) ? $role.'.' : '';
    }
}
