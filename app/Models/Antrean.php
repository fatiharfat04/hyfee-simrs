<?php

namespace App\Models;

use App\Enums\AntreanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Antrean extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_antrean',
        'nomor_urut',
        'pasien_id',
        'dokter_id',
        'poli_id',
        'jadwal_dokter_id',
        'tanggal_antrean',
        'jam_daftar',
        'jam_dipanggil',
        'jam_selesai',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'nomor_urut' => 'integer',
            'tanggal_antrean' => 'date',
            'jam_daftar' => 'datetime',
            'jam_dipanggil' => 'datetime',
            'jam_selesai' => 'datetime',
            'status' => AntreanStatus::class,
        ];
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class);
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class);
    }

    public function jadwalDokter(): BelongsTo
    {
        return $this->belongsTo(JadwalDokter::class);
    }
}
