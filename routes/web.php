<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DokterController;
use App\Http\Controllers\Admin\JadwalDokterController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PasienController as AdminPasienController;
use App\Http\Controllers\Admin\PoliController;
use App\Http\Controllers\Dokter\AntreanController as DokterAntreanController;
use App\Http\Controllers\Dokter\DashboardController as DokterDashboardController;
use App\Http\Controllers\PapanAntreanController;
use App\Http\Controllers\Pasien\AntreanController;
use App\Http\Controllers\Pasien\DashboardController as PasienDashboardController;
use App\Http\Controllers\Pasien\NotifikasiController;
use App\Http\Controllers\Pasien\ProfilController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Struktur route mengikuti project.md Bab 6: prefix + name per role.
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect(auth()->user()->dashboardRoute())
        : view('welcome');
})->name('home');

// Publik, tanpa auth (project.md Bab 6) — dipasang di TV rumah sakit.
Route::get('/papan-antrean/{poli}', [PapanAntreanController::class, 'show'])->name('papan.show');

// Admin
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('poli', PoliController::class);
        Route::resource('dokter', DokterController::class);
        Route::resource('jadwal', JadwalDokterController::class)->except(['show']);

        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/export', [LaporanController::class, 'export'])->name('laporan.export');

        Route::get('pasien', [AdminPasienController::class, 'index'])->name('pasien.index');
        Route::get('pasien/{pasien}/edit', [AdminPasienController::class, 'edit'])->name('pasien.edit');
        Route::put('pasien/{pasien}', [AdminPasienController::class, 'update'])->name('pasien.update');
    });

// Dokter
Route::middleware(['auth', 'role:dokter'])
    ->prefix('dokter')
    ->name('dokter.')
    ->group(function () {
        Route::get('dashboard', [DokterDashboardController::class, 'index'])->name('dashboard');

        Route::post('antrean/{antrean}/panggil', [DokterAntreanController::class, 'panggil'])->name('antrean.panggil');
        Route::post('antrean/{antrean}/selesai', [DokterAntreanController::class, 'selesai'])->name('antrean.selesai');
    });

// Pasien
Route::middleware(['auth', 'role:pasien'])
    ->prefix('pasien')
    ->name('pasien.')
    ->group(function () {
        Route::get('dashboard', [PasienDashboardController::class, 'index'])->name('dashboard');

        Route::get('daftar-antrean', [AntreanController::class, 'create'])->name('antrean.create');
        Route::post('daftar-antrean', [AntreanController::class, 'store'])->name('antrean.store');
        Route::get('antrean-saya', [AntreanController::class, 'mine'])->name('antrean.mine');
        Route::post('antrean/{antrean}/batal', [AntreanController::class, 'cancel'])->name('antrean.cancel');

        Route::get('profil', [ProfilController::class, 'edit'])->name('profil.edit');
        Route::put('profil', [ProfilController::class, 'update'])->name('profil.update');

        // Lonceng notifikasi (Bab 5.2)
        Route::post('notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.bacaSemua');
        Route::post('notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');
    });

// Profil (semua role)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
