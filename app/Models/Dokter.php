<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dokter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'poli_id',
        'no_sip',
        'gelar_depan',
        'gelar_belakang',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class);
    }

    public function jadwalDokters(): HasMany
    {
        return $this->hasMany(JadwalDokter::class);
    }

    public function antreans(): HasMany
    {
        return $this->hasMany(Antrean::class);
    }

    /**
     * Nama lengkap tampil, contoh: "dr. Andini Pratiwi, Sp.A"
     */
    public function namaLengkap(): string
    {
        return trim(implode(' ', array_filter([
            $this->gelar_depan,
            $this->user?->name,
            $this->gelar_belakang,
        ])));
    }
}
