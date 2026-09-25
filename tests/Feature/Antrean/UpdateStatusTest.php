<?php

namespace Tests\Feature\Antrean;

use App\Enums\AntreanStatus;
use App\Exceptions\AntreanException;
use App\Models\Antrean;
use App\Models\Pasien;
use App\Services\QueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FixturesAntrean;
use Tests\TestCase;

/**
 * Bab 8 checklist #4 dan #5:
 * - transisi status ilegal (menunggu → selesai langsung) melempar exception
 * - dokter A tidak bisa mengubah status antrean milik dokter B (AntreanPolicy)
 */
class UpdateStatusTest extends TestCase
{
    use RefreshDatabase, FixturesAntrean;

    public function test_transisi_status_lompat_dilempar_exception(): void
    {
        [$poli, $dokter, $jadwal] = $this->buatPoliDanDokter();
        $antrean = $this->daftarkan(
            Pasien::factory()->create(),
            $dokter,
            $poli,
            $jadwal,
            today()->toDateString(),
        );

        try {
            app(QueueService::class)->updateStatus($antrean, AntreanStatus::SELESAI);
            $this->fail('Transisi menunggu → selesai seharusnya ditolak.');
        } catch (AntreanException $exception) {
            $this->assertSame(
                "Perubahan status dari 'menunggu' ke 'selesai' tidak diizinkan.",
                $exception->getMessage(),
            );
        }

        // Transaksi dibatalkan penuh — status tidak berubah sebagian.
        $this->assertSame(AntreanStatus::MENUNGGU, $antrean->fresh()->status);
    }

    public function test_transisi_status_normal_berjalan_sesuai_state_machine(): void
    {
        [$poli, $dokter, $jadwal] = $this->buatPoliDanDokter();
        $antrean = $this->daftarkan(
            Pasien::factory()->create(),
            $dokter,
            $poli,
            $jadwal,
            today()->toDateString(),
        );

        $service = app(QueueService::class);
        $service->updateStatus($antrean, AntreanStatus::DIPANGGIL);
        $service->updateStatus($antrean, AntreanStatus::DILAYANI);
        $service->updateStatus($antrean, AntreanStatus::SELESAI);

        $antrean->refresh();

        $this->assertSame(AntreanStatus::SELESAI, $antrean->status);
        $this->assertNotNull($antrean->jam_dipanggil);
        $this->assertNotNull($antrean->jam_selesai);
    }

    public function test_dokter_hanya_melayani_satu_pasien_dalam_satu_waktu(): void
    {
        [$poli, $dokter, $jadwal] = $this->buatPoliDanDokter();
        $tanggal = today()->toDateString();

        $pertama = $this->daftarkan(Pasien::factory()->create(), $dokter, $poli, $jadwal, $tanggal);
        $kedua = $this->daftarkan(Pasien::factory()->create(), $dokter, $poli, $jadwal, $tanggal);

        app(QueueService::class)->updateStatus($pertama, AntreanStatus::DIPANGGIL);

        try {
            app(QueueService::class)->updateStatus($kedua, AntreanStatus::DIPANGGIL);
            $this->fail('Antrean kedua tidak boleh dipanggil sebelum yang pertama selesai.');
        } catch (AntreanException $exception) {
            $this->assertSame(
                "Selesaikan antrean {$pertama->kode_antrean} yang sedang dilayani terlebih dahulu.",
                $exception->getMessage(),
            );
        }

        $this->assertSame(AntreanStatus::MENUNGGU, $kedua->fresh()->status);
    }

    public function test_dokter_a_tidak_bisa_mengubah_status_antrean_dokter_b(): void
    {
        [$poliA, $dokterA, $jadwalA] = $this->buatPoliDanDokter();
        [, $dokterB] = $this->buatPoliDanDokter();

        $antreanA = $this->daftarkan(
            Pasien::factory()->create(),
            $dokterA,
            $poliA,
            $jadwalA,
            today()->toDateString(),
        );

        // Otorisasi per-record (project.md Bab 5.3) — middleware role saja tidak cukup.
        $this->assertTrue($dokterA->user->can('call', $antreanA));
        $this->assertFalse($dokterB->user->can('call', $antreanA));

        // Lewat HTTP pun tetap ditolak.
        $this->actingAs($dokterB->user)
            ->post(route('dokter.antrean.panggil', $antreanA))
            ->assertForbidden();

        $this->assertSame(AntreanStatus::MENUNGGU, $antreanA->fresh()->status);

        // Kontrol positif: dokter pemilik boleh memanggil.
        $this->actingAs($dokterA->user)
            ->post(route('dokter.antrean.panggil', $antreanA))
            ->assertRedirect();

        $this->assertSame(AntreanStatus::DIPANGGIL, $antreanA->fresh()->status);

        // Dokter B tidak terpengaruh oleh percobaan tersebut.
        $this->assertSame(0, Antrean::query()->where('dokter_id', $dokterB->id)->count());
    }
}
