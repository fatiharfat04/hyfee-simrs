<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->syncPrimaryRoleId();
        $this->registerLivewireComponents();
    }

    /**
     * Komponen Livewire dipakai untuk panel antrean dokter (Prompt 5)
     * yang ter-update tanpa reload halaman.
     */
    private function registerLivewireComponents(): void
    {
        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }

        \Livewire\Livewire::component('antrean-panel', \App\Livewire\Dokter\AntreanPanel::class);
    }

    /**
     * Menjaga kolom users.role_id (project.md Bab 3.1) tetap sinkron
     * dengan role spatie — spatie tetap jadi sumber otorisasi.
     *
     * (permission.events_enabled harus true.)
     */
    private function syncPrimaryRoleId(): void
    {
        $handle = function ($event): void {
            $model = $event->model ?? null;

            if (! $model instanceof User) {
                return;
            }

            $primary = $model->roles()->pluck('roles.name')->first();

            $roleId = $primary
                ? \App\Models\Role::query()->where('name', $primary)->value('id')
                : null;

            if ($model->getAttribute('role_id') !== $roleId) {
                $model->forceFill(['role_id' => $roleId])->saveQuietly();
            }
        };

        Event::listen(RoleAttached::class, $handle);
        Event::listen(RoleDetached::class, $handle);
    }
}
