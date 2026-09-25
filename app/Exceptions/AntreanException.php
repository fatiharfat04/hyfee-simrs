<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Error bisnis pada proses antrean (pesan sudah dalam Bahasa Indonesia
 * sehingga aman ditampilkan langsung ke user).
 */
class AntreanException extends RuntimeException
{
    public static function jadwalTidakTersedia(): self
    {
        return new self('Dokter tersebut tidak memiliki jadwal praktik hari ini.');
    }

    public static function kuotaPenuh(): self
    {
        return new self('Kuota antrean pada sesi ini sudah penuh. Silakan pilih jadwal lain.');
    }

    public static function sudahTerdaftar(): self
    {
        return new self('Anda sudah memiliki antrean aktif untuk dokter ini hari ini.');
    }

    public static function dokterTidakAktif(): self
    {
        return new self('Poli atau dokter yang dipilih sedang tidak aktif.');
    }

    public static function transisiTidakValid(string $dari, string $ke): self
    {
        return new self("Perubahan status dari '{$dari}' ke '{$ke}' tidak diizinkan.");
    }

    /**
     * Panel dokter hanya melayani satu pasien dalam satu waktu
     * (project.md Bab 7.5 — 1 panel besar berisi antrean saat ini).
     */
    public static function masihAdaYangDilayani(string $kodeAntrean): self
    {
        return new self("Selesaikan antrean {$kodeAntrean} yang sedang dilayani terlebih dahulu.");
    }
}
