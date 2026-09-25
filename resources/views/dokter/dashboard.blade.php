<x-layouts.dokter :title="'Dashboard Dokter'">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Antrean Hari Ini</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Selamat bertugas, {{ $user->name }}
                    @if ($user->dokter)
                        · {{ $user->dokter->namaLengkap() }}
                    @endif
                    · {{ now()->translatedFormat('l, d F Y') }}
                </p>
            </div>

            @if ($user->dokter?->poli)
                <span class="rounded-full bg-primary/10 px-3 py-1 text-sm font-semibold text-primary">
                    {{ $user->dokter->poli->nama_poli }} · {{ $user->dokter->poli->lokasi_ruang }}
                </span>
            @endif
        </div>

        {{-- Panel antrean real-time (Livewire) — project.md Prompt 5 --}}
        <livewire:antrean-panel />
    </div>
</x-layouts.dokter>
