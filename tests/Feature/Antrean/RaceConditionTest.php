<?php

namespace Tests\Feature\Antrean;

use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\Poli;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;
use Tests\Support\FixturesAntrean;
use Tests\TestCase;

/**
 * Bab 8 checklist #3: race condition — dua pendaftaran bersamaan tidak boleh
 * menghasilkan nomor antrean duplikat.
 *
 * Fixture harus benar-benar sudah COMMIT, karena pendaftaran dijalankan oleh
 * dua proses PHP terpisah. Karena itu kelas ini TIDAK memakai RefreshDatabase
 * (yang membungkus tiap uji dalam transaksi yang tidak terlihat proses lain);
 * skema dibersihkan manual lewat migrate:fresh.
 */
class RaceConditionTest extends TestCase
{
    use FixturesAntrean;

    private const JUMLAH_PER_PROSES = 5;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->seed(RoleSeeder::class);
    }

    protected function tearDown(): void
    {
        // Buang fixture yang ter-commit agar uji berikutnya kembali bersih.
        $this->artisan('migrate:fresh');

        parent::tearDown();
    }

    public function test_pendaftaran_bersamaan_tidak_menghasilkan_nomor_antrean_duplikat(): void
    {
        [$poli, $dokter] = $this->buatPoliDanDokter(['kuota' => 50]);
        $tanggal = today()->addDay()->toDateString();

        $kiri = Pasien::factory()->count(self::JUMLAH_PER_PROSES)->create()->pluck('id');
        $kanan = Pasien::factory()->count(self::JUMLAH_PER_PROSES)->create()->pluck('id');

        // Kedua proses dijalankan dulu, baru ditunggu — tumpang tindih nyata.
        $prosesKiri = $this->prosesDaftar($dokter, $poli, $tanggal, $kiri);
        $prosesKanan = $this->prosesDaftar($dokter, $poli, $tanggal, $kanan);

        $nomor = [];
        $gagal = [];

        foreach ([$prosesKiri, $prosesKanan] as $proses) {
            $proses->wait();

            $this->assertSame(
                0,
                $proses->getExitCode(),
                'Proses paralel gagal: '.$proses->getErrorOutput().$proses->getOutput(),
            );

            foreach ($this->bacaHasil($proses) as $baris) {
                if (isset($baris['gagal'])) {
                    $gagal[] = $baris['gagal'];

                    continue;
                }

                $nomor[] = $baris['nomor'];
            }
        }

        // Tidak ada satu pun pendaftaran yang ditolak.
        $this->assertSame([], $gagal, 'Pendaftaran gagal: '.implode(' | ', $gagal));

        // Semua nomor terpakai tepat satu kali, tanpa duplikat.
        $this->assertCount(2 * self::JUMLAH_PER_PROSES, $nomor);

        sort($nomor);
        $this->assertSame(range(1, 2 * self::JUMLAH_PER_PROSES), $nomor);

        // Sudut pandang database — memastikan benar-benar tidak ada baris dobel.
        $this->assertSame(2 * self::JUMLAH_PER_PROSES, Antrean::query()->count());
        $this->assertSame(
            2 * self::JUMLAH_PER_PROSES,
            Antrean::query()
                ->where('poli_id', $poli->id)
                ->whereDate('tanggal_antrean', $tanggal)
                ->distinct()
                ->count('nomor_urut'),
        );
        $this->assertSame(
            2 * self::JUMLAH_PER_PROSES,
            Antrean::query()->distinct()->count('kode_antrean'),
        );
    }

    private function prosesDaftar(Dokter $dokter, Poli $poli, string $tanggal, Collection $pasienIds): Process
    {
        $proses = new Process(
            [
                PHP_BINARY,
                'tests/Fixtures/daftar_paralel.php',
                (string) $dokter->id,
                (string) $poli->id,
                $tanggal,
                $pasienIds->implode(','),
            ],
            base_path(),
            array_merge(getenv(), ['APP_ENV' => 'testing']),
        );

        $proses->setTimeout(120);
        $proses->start();

        return $proses;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function bacaHasil(Process $proses): array
    {
        preg_match('/###HASIL###(.*)/', $proses->getOutput(), $cocok);

        $hasil = json_decode(trim($cocok[1] ?? '[]'), true);

        $this->assertIsArray($hasil, 'Output proses tidak valid: '.$proses->getOutput());

        return $hasil;
    }
}
