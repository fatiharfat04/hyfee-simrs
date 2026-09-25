<?php

namespace App\Livewire\Dokter;

use App\Enums\AntreanStatus;
use App\Exceptions\AntreanException;
use App\Models\Antrean;
use App\Models\Dokter;
use App\Services\QueueService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Panel antrean dokter (project.md Prompt 5 + Bab 7.5).
 *
 * Seluruh aksi (panggil / selesai / tidak hadir / panggil ulang) tetap
 * melewati AntreanPolicy + QueueService — controller/Livewire hanya
 * meneruskan, tidak menghitung nomor ataupun transisi status sendiri.
 */
#[Layout('components.layouts.dokter')]
class AntreanPanel extends Component
{
    /** Target aksi yang menunggu konfirmasi modal. */
    public ?int $targetId = null;

    /** Aksi yang dipilih: panggil | panggil_berikutnya | selesai | tidak_hadir | panggil_ulang. */
    public ?string $aksi = null;

    /** Pesan sukses / galat sementara (Bahasa Indonesia). */
    public ?string $pesan = null;

    public ?string $galat = null;

    public function mount(): void
    {
        //
    }

    /**
     * Aksi utama: panggil nomor menunggu terkecil.
     */
    public function panggilBerikutnya(): void
    {
        $this->jalankan('panggil_berikutnya', null);
    }

    /**
     * Aksi per-baris sesuai route Bab 6 (panggil / selesai / dsb).
     */
    public function jalankan(string $aksi, ?int $antreanId): void
    {
        $this->resetPesan();
        $this->aksi = $aksi;
        $this->targetId = $antreanId;

        $dokter = auth()->user()->dokter;

        try {
            match ($aksi) {
                'panggil_berikutnya' => $this->prosesPanggilBerikutnya($dokter),
                'panggil', 'panggil_ulang' => $this->prosesPanggil($dokter, $antreanId),
                'selesai' => $this->prosesSelesai($dokter, $antreanId),
                'tidak_hadir' => $this->prosesTidakHadir($dokter, $antreanId),
                default => throw AntreanException::transisiTidakValid('-', $aksi),
            };
        } catch (AntreanException $exception) {
            $this->galat = $exception->getMessage();
        }

        // Modal ditutup dari sisi client setelah sukses.
        $this->dispatch('modalTutup');
    }

    /** Tutup modal konfirmasi tanpa aksi. */
    public function batal(): void
    {
        $this->resetPesan();
        $this->targetId = null;
        $this->aksi = null;
        $this->dispatch('modalTutup');
    }

    private function prosesPanggilBerikutnya(Dokter $dokter): void
    {
        $berikut = Antrean::query()
            ->where('dokter_id', $dokter->id)
            ->whereDate('tanggal_antrean', today())
            ->where('status', AntreanStatus::MENUNGGU->value)
            ->orderBy('nomor_urut')
            ->first();

        if (! $berikut) {
            throw new AntreanException('Tidak ada antrean menunggu untuk Anda hari ini.');
        }

        $this->authorize('call', $berikut);

        $dipanggil = app(QueueService::class)->panggilBerikutnya($dokter, today()->toDateString());

        if ($dipanggil) {
            $this->pesan = "Nomor {$dipanggil->kode_antrean} dipanggil.";
        }
    }

    private function prosesPanggil(Dokter $dokter, ?int $antreanId): void
    {
        $antrean = $this->cariDanAuthorize($antreanId);

        app(QueueService::class)->updateStatus($antrean, AntreanStatus::DIPANGGIL);
        $this->pesan = "Nomor {$antrean->kode_antrean} dipanggil.";
    }

    private function prosesSelesai(Dokter $dokter, ?int $antreanId): void
    {
        $antrean = $this->cariDanAuthorize($antreanId);

        $service = app(QueueService::class);

        // Transisi mengikuti state machine Bab 4 — tidak boleh melompat.
        if ($antrean->status === AntreanStatus::DIPANGGIL) {
            $service->updateStatus($antrean, AntreanStatus::DILAYANI);
        }

        $service->updateStatus($antrean, AntreanStatus::SELESAI);

        $this->pesan = "Antrean {$antrean->kode_antrean} selesai dilayani.";
    }

    private function prosesTidakHadir(Dokter $dokter, ?int $antreanId): void
    {
        $antrean = $this->cariDanAuthorize($antreanId);

        app(QueueService::class)->updateStatus($antrean, AntreanStatus::TIDAK_HADIR);

        $this->pesan = "Antrean {$antrean->kode_antrean} ditandai tidak hadir.";
    }

    private function cariDanAuthorize(?int $antreanId): Antrean
    {
        $antrean = Antrean::query()->findOrFail($antreanId);

        // Otorisasi per-record (project.md Bab 5.3).
        $this->authorize('call', $antrean);

        return $antrean;
    }

    /** Bersihkan notifikasi (dipanggil via wire:click). */
    public function resetPesan(): void
    {
        $this->pesan = null;
        $this->galat = null;
        $this->targetId = null;
        $this->aksi = null;
    }

    public function render(): mixed
    {
        $dokter = auth()->user()->dokter;

        $dasar = fn () => Antrean::query()
            ->where('dokter_id', $dokter?->id)
            ->whereDate('tanggal_antrean', today());

        return view('livewire.dokter.antrean-panel', [
            'dokter' => $dokter,
            'aktif' => $dasar()
                ->whereIn('status', [AntreanStatus::DIPANGGIL->value, AntreanStatus::DILAYANI->value])
                ->orderBy('nomor_urut')
                ->with('pasien.user')
                ->first(),
            'menunggu' => $dasar()
                ->where('status', AntreanStatus::MENUNGGU->value)
                ->orderBy('nomor_urut')
                ->with('pasien.user')
                ->get(),
            'riwayat' => $dasar()
                ->whereIn('status', [AntreanStatus::SELESAI->value, AntreanStatus::TIDAK_HADIR->value])
                ->orderBy('nomor_urut')
                ->with('pasien.user')
                ->get(),
            'rekap' => [
                'menunggu' => $dasar()->where('status', AntreanStatus::MENUNGGU->value)->count(),
                'selesai' => $dasar()->where('status', AntreanStatus::SELESAI->value)->count(),
                'tidak_hadir' => $dasar()->where('status', AntreanStatus::TIDAK_HADIR->value)->count(),
            ],
            'aksi' => $this->aksi,
        ]);
    }
}
