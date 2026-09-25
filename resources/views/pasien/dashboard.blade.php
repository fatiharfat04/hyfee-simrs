<x-layouts.pasien :title="'Beranda'">
    <div class="space-y-4">
        <x-flash />

        <div>
            <h1 class="text-lg font-semibold">Halo, {{ $user->name }}</h1>
            <p class="text-sm text-slate-500">Daftar antrean rawat jalan tanpa perlu mengantre di loket.</p>
        </div>

        @if (! $pasien)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-800">Data diri belum lengkap</p>
                <p class="mt-1 text-xs text-amber-700">Isi data diri agar dapat mengambil nomor antrean.</p>
                <a href="{{ route('pasien.profil.edit') }}" class="mt-2 inline-block rounded-lg bg-amber-600 px-3 py-2 text-xs font-semibold text-white">Lengkapi Sekarang</a>
            </div>
        @endif

        @if ($antreanHariIni)
            <a href="{{ route('pasien.antrean.mine') }}" class="block rounded-2xl bg-primary p-5 text-white shadow">
                <p class="text-xs uppercase tracking-wide text-white/70">Antrean hari ini</p>
                <div class="mt-1 flex items-end justify-between gap-3">
                    <div>
                        <p class="font-mono text-5xl font-bold leading-none">{{ $antreanHariIni->kode_antrean }}</p>
                        <p class="mt-2 text-sm text-white/85">{{ $antreanHariIni->poli?->nama_poli }} · {{ $antreanHariIni->dokter?->namaLengkap() }}</p>
                    </div>
                    <span class="mb-1 rounded-full bg-white/20 px-3 py-1 text-sm font-semibold">{{ $antreanHariIni->status->label() }}</span>
                </div>
            </a>
        @endif

        <div class="grid grid-cols-2 gap-3">
            <x-card>
                <p class="text-xs text-slate-500">Total Antrean</p>
                <p class="mt-1 text-2xl font-bold text-primary">{{ $totalAntrean }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-slate-500">Sudah Selesai</p>
                <p class="mt-1 text-2xl font-bold text-success">{{ $selesai }}</p>
            </x-card>
        </div>

        <div class="grid gap-3">
            <a href="{{ route('pasien.antrean.create') }}"
               class="flex items-center justify-between rounded-xl border-2 border-primary bg-white p-4 shadow-sm transition hover:bg-primary/5">
                <span>
                    <span class="block text-sm font-semibold text-slate-800">Ambil Nomor Antrean</span>
                    <span class="block text-xs text-slate-500">Pilih poli & dokter yang berjadwal hari ini</span>
                </span>
                <span class="text-2xl text-primary">&rarr;</span>
            </a>

            <a href="{{ route('pasien.antrean.mine') }}"
               class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <span>
                    <span class="block text-sm font-semibold text-slate-800">Antrean Saya</span>
                    <span class="block text-xs text-slate-500">Lihat status & riwayat antrean</span>
                </span>
                <span class="text-2xl text-slate-400">&rarr;</span>
            </a>
        </div>
    </div>
</x-layouts.pasien>
