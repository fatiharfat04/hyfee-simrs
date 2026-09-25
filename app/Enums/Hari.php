<?php

namespace App\Enums;

/**
 * Hari praktik pada jadwal_dokters.hari (project.md Bab 3.6).
 */
enum Hari: string
{
    case SENIN = 'senin';
    case SELASA = 'selasa';
    case RABU = 'rabu';
    case KAMIS = 'kamis';
    case JUMAT = 'jumat';
    case SABTU = 'sabtu';
    case MINGGU = 'minggu';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Nama hari sesuai APP_TIMEZONE (Asia/Jakarta), dipakai untuk
     * mencocokkan jadwal dengan tanggal antrean.
     */
    public static function today(): self
    {
        return self::from(strtolower(now()->translatedFormat('l')));
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
