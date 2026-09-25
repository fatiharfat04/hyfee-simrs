<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * project.md Bab 5.1 — resetHarian() bersifat OPSIONAL: penomoran sudah
 * di-scope per tanggal_antrean, jadi job ini murni arsip/cleanup antrean
 * yang statusnya sudah final. Jadwalnya mengikuti QUEUE_RESET_HOUR (.env).
 */
Schedule::command('queue:reset-harian')
    ->dailyAt((string) config('queue.reset_hour', '00:00'));
