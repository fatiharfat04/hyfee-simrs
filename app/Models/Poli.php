<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poli extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_poli',
        'nama_poli',
        'prefix_antrean',
        'lokasi_ruang',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function dokters(): HasMany
    {
        return $this->hasMany(Dokter::class);
    }

    public function antreans(): HasMany
    {
        return $this->hasMany(Antrean::class);
    }
}
