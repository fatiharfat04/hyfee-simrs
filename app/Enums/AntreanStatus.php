<?php

namespace App\Enums;

/**
 * Status antrean + state machine (project.md Bab 4).
 */
enum AntreanStatus: string
{
    case MENUNGGU = 'menunggu';
    case DIPANGGIL = 'dipanggil';
    case DILAYANI = 'dilayani';
    case SELESAI = 'selesai';
    case BATAL = 'batal';
    case TIDAK_HADIR = 'tidak_hadir';

    /**
     * Label yang tampil ke user (Bahasa Indonesia).
     */
    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU => 'Menunggu',
            self::DIPANGGIL => 'Dipanggil',
            self::DILAYANI => 'Dilayani',
            self::SELESAI => 'Selesai',
            self::BATAL => 'Batal',
            self::TIDAK_HADIR => 'Tidak Hadir',
        };
    }

    /**
     * Transisi yang diizinkan — tidak boleh lompat (project.md Bab 4).
     *
     * menunggu  → dipanggil | batal
     * dipanggil → dilayani  | tidak_hadir
     * dilayani  → selesai
     * tidak_hadir → dipanggil (panggil ulang manual)
     * selesai / batal → tidak ada (final)
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::MENUNGGU => [self::DIPANGGIL, self::BATAL],
            self::DIPANGGIL => [self::DILAYANI, self::TIDAK_HADIR],
            self::DILAYANI => [self::SELESAI],
            self::TIDAK_HADIR => [self::DIPANGGIL],
            self::SELESAI, self::BATAL => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Status yang dianggap "antrean aktif" — dipakai validasi
     * double booking (project.md Bab 3.7 & 5.1).
     *
     * @return array<self>
     */
    public static function aktif(): array
    {
        return [self::MENUNGGU, self::DIPANGGIL, self::DILAYANI];
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
