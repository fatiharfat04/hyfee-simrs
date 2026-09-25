<x-layouts.admin :title="'Dashboard'">
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-semibold">Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Selamat datang, {{ $user->name }}.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Poli Aktif', 'value' => $poliCount ?? 0],
                ['label' => 'Dokter Aktif', 'value' => $dokterCount ?? 0],
                ['label' => 'Antrean Hari Ini', 'value' => $antreanHariIni ?? 0],
                ['label' => 'Sudah Dilayani', 'value' => $selesaiHariIni ?? 0],
            ] as $stat)
                <x-card>
                    <p class="text-sm text-slate-500">{{ $stat['label'] }}</p>
                    <p class="mt-1 text-3xl font-bold text-primary">{{ $stat['value'] }}</p>
                </x-card>
            @endforeach
        </div>

        <x-card>
            <h2 class="text-base font-semibold">Mulai dari sini</h2>
            <div class="mt-3 flex flex-wrap gap-2">
                @if (Route::has('admin.poli.index'))
                    <x-button variant="secondary" size="sm" onclick="location.href='{{ route('admin.poli.index') }}'">Kelola Poli</x-button>
                @endif
                @if (Route::has('admin.dokter.index'))
                    <x-button variant="secondary" size="sm" onclick="location.href='{{ route('admin.dokter.index') }}'">Kelola Dokter</x-button>
                @endif
                @if (Route::has('admin.jadwal.index'))
                    <x-button variant="secondary" size="sm" onclick="location.href='{{ route('admin.jadwal.index') }}'">Kelola Jadwal</x-button>
                @endif
                @if (Route::has('admin.laporan.index'))
                    <x-button size="sm" onclick="location.href='{{ route('admin.laporan.index') }}'">Lihat Laporan</x-button>
                @endif
            </div>
        </x-card>
    </div>
</x-layouts.admin>
