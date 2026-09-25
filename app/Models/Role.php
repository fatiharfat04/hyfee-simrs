<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Model role (tabel `roles` dari spatie/laravel-permission).
 * Relasi `users()` mengikuti definisi parent (belongsToMany via pivot).
 */
class Role extends SpatieRole
{
    use HasFactory;
}
