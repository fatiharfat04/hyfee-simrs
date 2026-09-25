<?php

namespace App\Models;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Pasien extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'no_rm',
        'nik',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_telp',
        'golongan_darah',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'jenis_kelamin' => JenisKelamin::class,
            'golongan_darah' => GolonganDarah::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $pasien): void {
            if (! $pasien->no_rm) {
                $pasien->no_rm = static::generateNoRm();
            }
        });
    }

    /**
     * Auto-generate no_rm format RM-YYYYMM-0001 (project.md Bab 3.3).
     * Transaction + lockForUpdate agar tidak tabrakan saat registrasi bersamaan.
     */
    public static function generateNoRm(?\DateTimeInterface $date = null): string
    {
        $prefix = 'RM-'.($date ? date('Ym', $date->getTimestamp()) : now()->format('Ym')).'-';

        return DB::transaction(function () use ($prefix) {
            $last = static::query()
                ->where('no_rm', 'like', $prefix.'%')
                ->orderByDesc('no_rm')
                ->lockForUpdate()
                ->value('no_rm');

            $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

            return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function antreans(): HasMany
    {
        return $this->hasMany(Antrean::class);
    }
}
