<?php

namespace Tests\Feature;

use App\Models\Pasien;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FixturesAntrean;
use Tests\TestCase;

/**
 * Bab 8 checklist #7: papan antrean publik bisa diakses tanpa login
 * (project.md Prompt 6 — dipasang di TV rumah sakit).
 */
class PapanAntreanTest extends TestCase
{
    use RefreshDatabase, FixturesAntrean;

    public function test_papan_antrean_bisa_diakses_tanpa_login(): void
    {
        [$poli] = $this->buatPoliDanDokter();

        $response = $this->get(route('papan.show', $poli));

        $response->assertOk();
        $this->assertGuest();

        // Tidak ada form login / tombol masuk di papan.
        $response->assertDontSee('name="password"');
        $response->assertSee($poli->nama_poli);
        $response->assertSee($poli->lokasi_ruang, false);
    }

    public function test_papan_antrean_hanya_menampilkan_antrean_hari_ini(): void
    {
        [$poli, $dokter, $jadwal] = $this->buatPoliDanDokter();

        $hariIni = $this->daftarkan(
            Pasien::factory()->create(),
            $dokter,
            $poli,
            $jadwal,
            today()->toDateString(),
        );

        $besok = $this->daftarkan(
            Pasien::factory()->create(),
            $dokter,
            $poli,
            $jadwal,
            today()->addDay()->toDateString(),
        );

        $response = $this->get(route('papan.show', $poli));

        $response->assertOk();
        $response->assertSee($hariIni->kode_antrean);

        /*
         * Hanya antrean hari ini yang ikut ke papan. Catatan: kode besok
         * memang sama ('A-001') karena nomor reset per poli per tanggal —
         * karena itu dibuktikan lewat daftar `menunggu`, bukan teks.
         */
        $this->assertSame([$hariIni->kode_antrean], $response->viewData('menunggu'));
        $this->assertSame(1, $response->viewData('rekap')['menunggu']);
        $this->assertSame($besok->tanggal_antrean->toDateString(), today()->addDay()->toDateString());
    }

    public function test_papan_antrean_poli_tidak_dikenal_mengembalikan_404(): void
    {
        $this->get(route('papan.show', 99999))->assertNotFound();
    }
}
