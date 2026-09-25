<?php

namespace Tests\Feature;

use App\Enums\AntreanStatus;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Pasien;
use App\Models\Poli;
use App\Models\User;
use App\Notifications\GiliranDipanggilNotification;
use App\Notifications\PeringatanGiliranBerikutNotification;
use App\Services\QueueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Support\FixturesAntrean;
use Tests\TestCase;

/**
 * Bab 8 checklist #8: notifikasi terkirim saat status berubah jadi dipanggil.
 *
 * Channel sesuai project.md Bab 5.2 — database (wajib) + broadcast (toast
 * real-time di halaman "Antrean Saya").
 */
class NotifikasiTest extends TestCase
{
    use RefreshDatabase, FixturesAntrean;

    public function test_notifikasi_terkirim_saat_status_berubah_jadi_dipanggil(): void
    {
        Notification::fake();

        [$poli, $dokter, $jadwal] = $this->buatPoliDanDokter();
        $tanggal = today()->toDateString();

        [$satu, $antreanSatu] = $this->daftarkanUntuk($dokter, $poli, $jadwal, $tanggal);
        [$dua] = $this->daftarkanUntuk($dokter, $poli, $jadwal, $tanggal);
        [$tiga, $antreanTiga] = $this->daftarkanUntuk($dokter, $poli, $jadwal, $tanggal);

        $dipanggil = app(QueueService::class)->panggilBerikutnya($dokter, $tanggal);

        $this->assertNotNull($dipanggil);
        $this->assertSame($antreanSatu->id, $dipanggil->id);

        // Pemberitahuan giliran → pasien yang nomornya dipanggil.
        Notification::assertSentTo(
            $satu->user,
            GiliranDipanggilNotification::class,
            fn ($notification, $channels) => $channels === ['database', 'broadcast']
                && $notification->toArray($satu->user)['kode_antrean'] === $antreanSatu->kode_antrean,
        );

        // Peringatan dini → pasien nomor_urut = (dipanggil + 2).
        Notification::assertSentTo(
            $tiga->user,
            PeringatanGiliranBerikutNotification::class,
            fn ($notification, $channels) => $channels === ['database', 'broadcast']
                && $notification->toArray($tiga->user)['kode_antrean'] === $antreanTiga->kode_antrean,
        );

        // Nomor di antaranya tidak menerima apa pun.
        Notification::assertNotSentTo($dua->user, GiliranDipanggilNotification::class);
        Notification::assertNotSentTo($dua->user, PeringatanGiliranBerikutNotification::class);
    }

    public function test_channel_database_benar_benar_menulis_tabel_notifikasi(): void
    {
        [$poli, $dokter, $jadwal] = $this->buatPoliDanDokter();
        $tanggal = today()->toDateString();

        [$pasien] = $this->daftarkanUntuk($dokter, $poli, $jadwal, $tanggal);

        app(QueueService::class)->panggilBerikutnya($dokter, $tanggal);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $pasien->user->id,
            'type' => GiliranDipanggilNotification::class,
        ]);

        // Belum dibaca — lonceng di halaman pasien akan menyorotnya.
        $this->assertNull(
            $pasien->user->notifications()->where('type', GiliranDipanggilNotification::class)->first()->read_at,
        );
    }

    public function test_notifikasi_juga_terkirim_saat_panggil_dari_panel_per_baris(): void
    {
        Notification::fake();

        [$poli, $dokter, $jadwal] = $this->buatPoliDanDokter();
        $tanggal = today()->toDateString();

        [$pertama, $antreanPertama] = $this->daftarkanUntuk($dokter, $poli, $jadwal, $tanggal);
        [$kedua, $antreanKedua] = $this->daftarkanUntuk($dokter, $poli, $jadwal, $tanggal);

        // Rapikan yang pertama supaya aturan "satu pasien" mengizinkan panggil berikutnya.
        $service = app(QueueService::class);
        $service->updateStatus($antreanPertama, AntreanStatus::DIPANGGIL);
        $service->updateStatus($antreanPertama, AntreanStatus::DILAYANI);
        $service->updateStatus($antreanPertama, AntreanStatus::SELESAI);

        Notification::assertSentTo($pertama->user, GiliranDipanggilNotification::class);

        // Panggil per baris dari panel dokter.
        $service->updateStatus($antreanKedua, AntreanStatus::DIPANGGIL);

        Notification::assertSentTo(
            $kedua->user,
            GiliranDipanggilNotification::class,
            fn ($notification, $channels) => $channels === ['database', 'broadcast']
                && $notification->toArray($kedua->user)['kode_antrean'] === $antreanKedua->kode_antrean,
        );
    }

    /**
     * Satu pasien mendaftar, mengembalikan [Pasien, Antrean].
     *
     * @return array{0: Pasien, 1: \App\Models\Antrean}
     */
    private function daftarkanUntuk(Dokter $dokter, Poli $poli, JadwalDokter $jadwal, string $tanggal): array
    {
        $pasien = Pasien::factory()->create();

        return [$pasien, $this->daftarkan($pasien, $dokter, $poli, $jadwal, $tanggal)];
    }
}
