<?php

namespace Tests\Feature\Antrean;

use App\Enums\AntreanStatus;
use App\Enums\Hari;
use App\Exceptions\AntreanException;
use App\Models\Antrean;
use App\Models\Pasien;
use App\Services\QueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FixturesAntrean;
use Tests\TestCase;

/**
 * Bab 8 checklist #1, #2 dan #6:
 * - pasien tidak bisa daftar 2x antrean aktif (dokter & tanggal sama)
 * - nomor antrean generate benar dan reset per poli per tanggal
 * - kuota jadwal penuh menolak pendaftaran baru
 */
class DaftarAntreanTest extends TestCase
{
    use RefreshDatabase, FixturesAntrean;

    public function test_pasien_tidak_bisa_daftar_dua_kali_untuk_dokter_dan_tanggal_yang_sama(): void
    {
        [$poli, $dokter, $jadwal] = $this->buatPoliDanDokter();
        $pasien = Pasien::factory()->create();
        $tanggal = '2026-10-01';

        $this->daftarkan($pasien, $dokter, $poli, $jadwal, $tanggal);

        try {
            $this->daftarkan($pasien, $dokter, $poli, $jadwal, $tanggal);
            $this->fail('Pendaftaran kedua seharusnya ditolak.');
        } catch (AntreanException $exception) {
            $this->assertSame(
                'Anda sudah memiliki antrean aktif untuk dokter ini hari ini.',
                $exception->getMessage(),
            );
        }

        $this->assertSame(1, Antrean::query()->count());
    }

    public function test_pendaftaran_kedua_ditolak_lewat_http(): void
    {
        [$poli, $dokter, $jadwal] = $this->buatPoliDanDokter();
        $jadwal->update(['hari' => Hari::today(), 'jam_mulai' => '00:00', 'jam_selesai' => '23:59']);

        $pasien = Pasien::factory()->create();
        $pasien->user->assignRole('pasien');

        $payload = ['poli_id' => $poli->id, 'dokter_id' => $dokter->id];

        $this->actingAs($pasien->user)
            ->post(route('pasien.antrean.store'), $payload)
            ->assertRedirect(route('pasien.antrean.mine'));

        $this->assertSame(1, Antrean::query()->count());

        $this->actingAs($pasien->user)
            ->post(route('pasien.antrean.store'), $payload)
            ->assertSessionHas('error', 'Anda sudah memiliki antrean aktif untuk dokter ini hari ini.');

        $this->assertSame(1, Antrean::query()->count());
    }

    public function test_pasien_boleh_daftar_ulang_setelah_antrean_dibatalkan(): void
    {
        [$poli, $dokter, $jadwal] = $this->buatPoliDanDokter();
        $pasien = Pasien::factory()->create();
        $tanggal = '2026-10-01';

        $pertama = $this->daftarkan($pasien, $dokter, $poli, $jadwal, $tanggal);

        app(QueueService::class)->updateStatus($pertama, AntreanStatus::BATAL);

        $kedua = $this->daftarkan($pasien, $dokter, $poli, $jadwal, $tanggal);

        $this->assertSame(2, $kedua->nomor_urut);
        $this->assertSame(2, Antrean::query()->count());
    }

    public function test_nomor_antrean_generate_benar_dan_reset_per_poli_per_tanggal(): void
    {
        [$poliA, $dokterA, $jadwalA] = $this->buatPoliDanDokter();
        [$poliB, $dokterB, $jadwalB] = $this->buatPoliDanDokter();

        $tanggal = '2026-10-01';
        $tanggalLain = '2026-10-02';

        $a1 = $this->daftarkan(Pasien::factory()->create(), $dokterA, $poliA, $jadwalA, $tanggal);
        $a2 = $this->daftarkan(Pasien::factory()->create(), $dokterA, $poliA, $jadwalA, $tanggal);
        $b1 = $this->daftarkan(Pasien::factory()->create(), $dokterB, $poliB, $jadwalB, $tanggal);
        $a3 = $this->daftarkan(Pasien::factory()->create(), $dokterA, $poliA, $jadwalA, $tanggalLain);

        // Naik 1 per pendaftaran, dalam satu poli + tanggal.
        $this->assertSame([1, 2], [$a1->nomor_urut, $a2->nomor_urut]);
        $this->assertSame('A-001', $a1->kode_antrean);
        $this->assertSame('A-002', $a2->kode_antrean);

        // Poli berbeda → prefix & nomor sendiri, dimulai dari 1.
        $this->assertSame(1, $b1->nomor_urut);
        $this->assertSame('B-001', $b1->kode_antrean);

        // Tanggal berbeda → reset ke 1.
        $this->assertSame(1, $a3->nomor_urut);
        $this->assertSame('A-001', $a3->kode_antrean);

        $this->assertSame(4, Antrean::query()->count());
    }

    public function test_kuota_jadwal_penuh_menolak_pendaftaran_baru(): void
    {
        [$poli, $dokter, $jadwal] = $this->buatPoliDanDokter(['kuota' => 2]);
        $tanggal = '2026-10-01';

        $this->daftarkan(Pasien::factory()->create(), $dokter, $poli, $jadwal, $tanggal);
        $this->daftarkan(Pasien::factory()->create(), $dokter, $poli, $jadwal, $tanggal);

        try {
            $this->daftarkan(Pasien::factory()->create(), $dokter, $poli, $jadwal, $tanggal);
            $this->fail('Pendaftaran ke-3 seharusnya ditolak karena kuota penuh.');
        } catch (AntreanException $exception) {
            $this->assertSame(
                'Kuota antrean pada sesi ini sudah penuh. Silakan pilih jadwal lain.',
                $exception->getMessage(),
            );
        }

        // Antrean yang tercatat hanya untuk jadwal tersebut.
        $this->assertSame(2, Antrean::query()->where('jadwal_dokter_id', $jadwal->id)->count());
    }
}
