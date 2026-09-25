<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antreans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_antrean', 10);
            $table->unsignedInteger('nomor_urut');
            $table->foreignId('pasien_id')->constrained('pasiens');
            $table->foreignId('dokter_id')->constrained('dokters');
            $table->foreignId('poli_id')->constrained('polis');
            $table->foreignId('jadwal_dokter_id')->nullable()->constrained('jadwal_dokters')->nullOnDelete();
            $table->date('tanggal_antrean');
            $table->dateTime('jam_daftar');
            $table->dateTime('jam_dipanggil')->nullable();
            $table->dateTime('jam_selesai')->nullable();
            $table->enum('status', [
                'menunggu', 'dipanggil', 'dilayani', 'selesai', 'batal', 'tidak_hadir',
            ])->default('menunggu');
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Query papan antrean (project.md Bab 3.7)
            $table->index(['poli_id', 'tanggal_antrean', 'status'], 'antreans_papan_idx');
            $table->index(['dokter_id', 'tanggal_antrean', 'status'], 'antreans_dokter_idx');
            $table->index(['tanggal_antrean']);

            // Nomor urut unik per poli per tanggal
            $table->unique(['poli_id', 'tanggal_antrean', 'nomor_urut'], 'antreans_nomor_unik');

            /*
             * kode_antrean unik per hari (project.md Bab 3.3 & checklist Bab 8 #2).
             *
             * Spesifikasi mewajibkan nomor "reset per poli per tanggal"
             * ({prefix}-{3digit} → A-001 kembali muncul tiap hari), jadi
             * kode_antrean TIDAK boleh unique global. Unik ditegakkan per
             * tanggal_antrean: kode yang sama tidak boleh muncul dua kali
             * pada hari yang sama (juga menangkap dua poli ber-prefix sama).
             */
            $table->unique(['tanggal_antrean', 'kode_antrean'], 'antreans_kode_unik');
        });

        /*
         * Double booking (project.md Bab 3.7):
         * constraint unique kondisional tidak native di MySQL, jadi dipakai
         * generated column MySQL 8 — baris ber-status 'batal' menghasilkan NULL
         * sehingga boleh mendaftar ulang. Validasi tetap wajib di QueueService.
         */
        DB::statement("
            ALTER TABLE antreans
            ADD COLUMN booking_key VARCHAR(64)
                GENERATED ALWAYS AS (
                    CASE WHEN status = 'batal' THEN NULL
                         ELSE CONCAT_WS('_', pasien_id, dokter_id, tanggal_antrean)
                    END
                ) STORED,
            ADD UNIQUE KEY antreans_double_booking_unique (booking_key)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('antreans');
    }
};
