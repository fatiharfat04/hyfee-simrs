<?php

namespace App\Services;

use App\Enums\AntreanStatus;
use App\Enums\Hari;
use App\Events\AntreanDipanggil;
use App\Exceptions\AntreanException;
use App\Models\Antrean;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Pasien;
use App\Models\Poli;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * Seluruh logika bisnis antrean (project.md Bab 5.1).
 * Controller tidak boleh menghitung nomor / mengubah status sendiri.
 */
class QueueService
{
    /**
     * Generate nomor antrean berikutnya untuk suatu poli & tanggal.
     *
     * WAJIB memakai transaksi + lockForUpdate supaya 2 pendaftaran
     * bersamaan tidak menghasilkan nomor yang sama.
     *
     * @return array{nomor_urut: int, kode_antrean: string}
     */
    public function generateNomorAntrean(Poli $poli, string $tanggal): array
    {
        return DB::transaction(function () use ($poli, $tanggal) {
            $max = Antrean::query()
                ->where('poli_id', $poli->id)
                // Setara (bukan whereDate) supaya memakai indeks
                // (poli_id, tanggal_antrean, nomor_urut). whereDate memakai
                // fungsi date() sehingga indeks terbuang dan InnoDB harus
                // mengunci seluruh baris — pemicu deadlock.
                ->where('tanggal_antrean', $tanggal)
                ->lockForUpdate()
                ->max('nomor_urut');

            $nomorUrut = ((int) $max) + 1;

            return [
                'nomor_urut' => $nomorUrut,
                'kode_antrean' => sprintf('%s-%03d', $poli->prefix_antrean, $nomorUrut),
            ];
        });
    }

    /**
     * Pendaftaran antrean oleh pasien.
     *
     * Validasi:
     *  - jadwal dokter tersedia pada hari itu & belum lewat jam praktik
     *  - kuota sesi belum penuh
     *  - pasien belum punya antrean aktif (menunggu/dipanggil/dilayani)
     *    untuk dokter yang sama di tanggal yang sama
     *
     * @throws AntreanException
     */
    public function daftarAntrean(
        Pasien $pasien,
        Dokter $dokter,
        Poli $poli,
        string $tanggal,
        ?JadwalDokter $jadwalDokter = null,
    ): Antrean {
        if (! $dokter->is_active || ! $poli->is_active || $dokter->poli_id !== $poli->id) {
            throw AntreanException::dokterTidakAktif();
        }

        $jadwalDokter ??= $this->resolveJadwal($dokter, $tanggal);

        if (! $jadwalDokter) {
            throw AntreanException::jadwalTidakTersedia();
        }

        return DB::transaction(function () use ($pasien, $dokter, $poli, $tanggal, $jadwalDokter) {
            /*
             * 0. Mutex per-poli (project.md Bab 5.1 — transaksi + lockForUpdate).
             *
             * Mengunci SATU baris poli lebih dulu menyerialkan seluruh
             * pendaftaran poli yang sama. Tanpa ini, query FOR UPDATE pada
             * langkah 1-3 mengunci rentang indeks berbeda atas baris yang sama
             * sehingga dua request bersamaan memicu deadlock InnoDB
             * (SQLSTATE 1213 "Deadlock found"). Posisi kunci juga selalu
             * sama, jadi tidak ada urutan kunci yang bisa berputar.
             */
            Poli::query()->whereKey($poli->id)->lockForUpdate()->first();

            // 1. Cegah double booking (project.md Bab 3.7 & 5.1)
            //    Tanpa FOR UPDATE: rentang barisnya lebar, dan sudah cukup
            //    diserialisasi oleh mutex poli + generated column booking_key.
            $sudahAda = Antrean::query()
                ->where('pasien_id', $pasien->id)
                ->where('dokter_id', $dokter->id)
                ->whereDate('tanggal_antrean', $tanggal)
                ->whereIn('status', array_map(fn (AntreanStatus $s) => $s->value, AntreanStatus::aktif()))
                ->exists();

            if ($sudahAda) {
                throw AntreanException::sudahTerdaftar();
            }

            // 2. Kuota sesi — dihitung tanpa lock agar tidak menahan seluruh
            //    baris (query memakai jadwal_dokter_id yang tidak terindeks).
            $terpakai = Antrean::query()
                ->where('jadwal_dokter_id', $jadwalDokter->id)
                ->whereDate('tanggal_antrean', $tanggal)
                ->where('status', '!=', AntreanStatus::BATAL->value)
                ->count();

            if ($terpakai >= $jadwalDokter->kuota) {
                throw AntreanException::kuotaPenuh();
            }

            // 3. Nomor antrean — lockForUpdate WAJIB di query max (Bab 5.1),
            //    kini aman karena hanya satu transaksi per poli yang berjalan.
            $nomor = $this->generateNomorAntrean($poli, $tanggal);

            try {
                return Antrean::create([
                    'kode_antrean' => $nomor['kode_antrean'],
                    'nomor_urut' => $nomor['nomor_urut'],
                    'pasien_id' => $pasien->id,
                    'dokter_id' => $dokter->id,
                    'poli_id' => $poli->id,
                    'jadwal_dokter_id' => $jadwalDokter->id,
                    'tanggal_antrean' => $tanggal,
                    'jam_daftar' => now(),
                    'status' => AntreanStatus::MENUNGGU,
                ]);
            } catch (UniqueConstraintViolationException) {
                // Pengaman terakhir dari constraint DB (project.md Bab 3.7)
                throw AntreanException::sudahTerdaftar();
            }

            // Percobaan ulang otomatis bila MySQL tetap melempar deadlock.
        }, 3);
    }

    /**
     * Panggil nomor menunggu terkecil untuk dokter & tanggal tersebut.
     * Broadcast event AntreanDipanggil.
     */
    public function panggilBerikutnya(Dokter $dokter, string $tanggal): ?Antrean
    {
        $antrean = DB::transaction(function () use ($dokter, $tanggal) {
            // Satu pasien dalam satu waktu (Bab 7.5)
            // whereDate sengaja dihindari: predicate date() merusak indeks
            // (dokter_id, tanggal_antrean, status) dan mengubah query ini
            // jadi scan penuh ber-LOCK — mengundang deadlock.
            $this->assertBelumAdaYangDilayani(
                Antrean::query()
                    ->where('dokter_id', $dokter->id)
                    ->where('tanggal_antrean', $tanggal)
                    ->whereIn('status', [AntreanStatus::DIPANGGIL->value, AntreanStatus::DILAYANI->value])
                    ->orderBy('nomor_urut')
                    ->lockForUpdate()
                    ->first()
            );

            $antrean = Antrean::query()
                ->where('dokter_id', $dokter->id)
                ->where('tanggal_antrean', $tanggal)
                ->where('status', AntreanStatus::MENUNGGU->value)
                ->orderBy('nomor_urut')
                ->lockForUpdate()
                ->first();

            if ($antrean) {
                $antrean->update([
                    'status' => AntreanStatus::DIPANGGIL,
                    'jam_dipanggil' => now(),
                ]);
            }

            return $antrean;
        });

        if ($antrean) {
            $this->siarkanPanggilan($antrean);
        }

        return $antrean;
    }

    /**
     * Ubah status dengan validasi state machine (project.md Bab 4).
     * Transisi lompat akan melempar AntreanException.
     *
     * @throws AntreanException
     */
    public function updateStatus(Antrean $antrean, AntreanStatus|string $statusBaru): void
    {
        $statusBaru = $statusBaru instanceof AntreanStatus
            ? $statusBaru
            : AntreanStatus::from($statusBaru);

        DB::transaction(function () use ($antrean, $statusBaru): void {
            // Row-lock agar pengecekan & perubahan status atomik.
            $antrean = Antrean::query()->whereKey($antrean->id)->lockForUpdate()->first() ?? $antrean;

            $saatIni = $antrean->status;

            if (! $saatIni->canTransitionTo($statusBaru)) {
                throw AntreanException::transisiTidakValid($saatIni->value, $statusBaru->value);
            }

            $payload = ['status' => $statusBaru];

            if ($statusBaru === AntreanStatus::DIPANGGIL) {
                // Panggil ulang (tidak_hadir -> dipanggil) juga wajib
                // satu-satu: tidak boleh ada antrean LAIN yang masih dilayani.
                $this->assertBelumAdaYangDilayani(
                    Antrean::query()
                        ->where('dokter_id', $antrean->dokter_id)
                        // Setara bukan whereDate — indeks (dokter_id,
                        // tanggal_antrean, status) harus ikut terpakai agar
                        // kuncinya tidak menyebar ke seluruh tabel.
                        ->where('tanggal_antrean', $antrean->tanggal_antrean->toDateString())
                        ->whereIn('status', [AntreanStatus::DIPANGGIL->value, AntreanStatus::DILAYANI->value])
                        ->where('id', '!=', $antrean->id)
                        ->orderBy('nomor_urut')
                        ->lockForUpdate()
                        ->first()
                );

                $payload['jam_dipanggil'] = $antrean->jam_dipanggil ?? now();
            }

            if ($statusBaru === AntreanStatus::SELESAI) {
                $payload['jam_selesai'] = now();
            }

            $antrean->update($payload);
        });

        // Transaksi sudah commit — baru siarkan & notifikasi.
        if ($statusBaru === AntreanStatus::DIPANGGIL) {
            $this->siarkanPanggilan($antrean->refresh());
        }
    }

    /**
     * Sisi efek pemanggilan: broadcast ke papan antrean (Bab 5.1)
     * + notifikasi database/broadcast ke pasien (Bab 5.2).
     *
     * Wajib dipanggil SETELAH transaksi commit agar tidak ada
     * job broadcast/notifikasi yatim bila transaksi gagal.
     */
    private function siarkanPanggilan(Antrean $antrean): void
    {
        // event() (bukan ::dispatch()) supaya event ShouldBroadcast
        // diproses oleh event dispatcher -> BroadcastManager.
        event(new AntreanDipanggil($antrean));

        app(NotificationService::class)->kirimUntukPanggilan($antrean);
    }

    /**
     * Jalankan sinkronisasi (opsional): penomoran sudah di-scope per
     * tanggal_antrean, jadi reset ini murni untuk arsip/cleanup.
     * Dipanggil oleh scheduled command `queue:reset-harian`.
     *
     * @return int Jumlah antrean yang diarsipkan/dihapus
     */
    public function resetHarian(): int
    {
        $batas = now()->subDays((int) config('queue.reset_days', 30))->startOfDay();

        return Antrean::query()
            ->whereDate('tanggal_antrean', '<', $batas)
            ->whereIn('status', [
                AntreanStatus::SELESAI->value,
                AntreanStatus::BATAL->value,
                AntreanStatus::TIDAK_HADIR->value,
            ])
            ->delete();
    }

    /**
     * Guard: dokter hanya boleh memanggil satu pasien dalam satu waktu
     * (project.md Bab 7.5 — 1 panel besar berisi antrean saat ini).
     *
     * @throws AntreanException
     */
    private function assertBelumAdaYangDilayani(?Antrean $sedangAktif): void
    {
        if ($sedangAktif) {
            throw AntreanException::masihAdaYangDilayani($sedangAktif->kode_antrean);
        }
    }

    /**
     * Cari jadwal praktik dokter yang cocok untuk tanggal & jam sekarang.
     */
    public function resolveJadwal(Dokter $dokter, string $tanggal): ?JadwalDokter    {
        $hari = Hari::from(strtolower(\Illuminate\Support\Carbon::parse($tanggal)->translatedFormat('l')));

        $jadwals = JadwalDokter::query()
            ->where('dokter_id', $dokter->id)
            ->where('hari', $hari->value)
            ->where('is_active', true)
            ->orderBy('jam_mulai')
            ->get();

        if ($jadwals->isEmpty()) {
            return null;
        }

        $jamSekarang = now()->format('H:i:s');

        return $jadwals->first(fn (JadwalDokter $jadwal) => $jadwal->jam_mulai->format('H:i:s') <= $jamSekarang
            && $jamSekarang <= $jadwal->jam_selesai->format('H:i:s'))
            ?? $jadwals->first();
    }
}
