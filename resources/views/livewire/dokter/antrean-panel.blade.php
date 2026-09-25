<div class="space-y-6"
     wire:poll.20s
     x-init="setTimeout(() => { if ($wire.pesan || $wire.galat) $wire.resetPesan() }, 6000)"
     x-data="{
         open: false,
         aksi: '',
         id: null,
         konfirmasi() {
             this.open = false;
             $wire.jalankan(this.aksi, this.id);
         },
         batal() {
             this.open = false;
             $wire.batal();
         },
     }"
     x-on:konfirmasi.window="aksi = $event.detail.aksi; id = $event.detail.id; open = true"
     x-on:modalTutup.window="open = false">

    {{-- Notifikasi aksi (project.md Bab 7.2) --}}
    @if ($pesan)
        <div class="flex items-center justify-between gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
            <span>{{ $pesan }}</span>
            <button type="button" wire:click="resetPesan" class="text-green-600 hover:text-green-900">&times;</button>
        </div>
    @endif

    @if ($galat)
        <div class="flex items-center justify-between gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            <span>{{ $galat }}</span>
            <button type="button" wire:click="resetPesan" class="text-red-500 hover:text-red-800">&times;</button>
        </div>
    @endif

    {{-- Kartu utama: aksi utama berada di pusat (Bab 7.5) --}}
    <div class="rounded-xl border border-slate-200 bg-white p-6 text-center shadow-sm">
        @if ($aktif)
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Sedang dilayani</p>
            <p class="mt-2 font-mono text-7xl font-bold leading-none text-primary">{{ $aktif->kode_antrean }}</p>

            <div class="mt-4 flex flex-wrap items-center justify-center gap-3 text-sm text-slate-600">
                <span>{{ $aktif->pasien?->user?->name ?? 'Pasien' }}</span>
                <span class="text-slate-300">•</span>
                <span>Masuk {{ strtoupper($aktif->jam_daftar->format('H:i')) }} WIB</span>
                @if ($aktif->jam_dipanggil)
                    <span class="text-slate-300">•</span>
                    <span>Dipanggil {{ strtoupper($aktif->jam_dipanggil->format('H:i')) }} WIB</span>
                @endif
                <x-status-badge :status="$aktif->status->value" />
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-center gap-3">
                <x-button type="button"
                          variant="success"
                          size="lg"
                          x-on:click="$dispatch('konfirmasi', { aksi: 'selesai', id: {{ $aktif->id }} })">
                    Selesai
                </x-button>

                @if ($aktif->status->value === 'dipanggil')
                    <x-button type="button"
                              variant="secondary"
                              x-on:click="$dispatch('konfirmasi', { aksi: 'tidak_hadir', id: {{ $aktif->id }} })">
                        Tidak Hadir
                    </x-button>
                @endif
            </div>
        @else
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Antrean berikutnya</p>
            @if ($menunggu->isNotEmpty())
                <p class="mt-2 font-mono text-7xl font-bold leading-none text-slate-300">{{ $menunggu->first()->kode_antrean }}</p>
                <p class="mt-3 text-sm text-slate-500">Tekan tombol di bawah untuk memanggil nomor ini.</p>
            @else
                <p class="mt-3 font-mono text-6xl font-bold leading-none text-slate-200">—</p>
                <p class="mt-3 text-sm text-slate-500">Tidak ada antrean menunggu. Pasien akan muncul otomatis di sini.</p>
            @endif
        @endif

        {{-- Tombol paling dominan di layar (project.md Bab 7.5) --}}
        <div class="mt-6 border-t border-slate-100 pt-5">
            @if ($aktif)
                <x-button type="button" size="lg" class="w-full sm:w-auto sm:min-w-72" disabled>
                    Panggil Berikutnya
                </x-button>
                <p class="mt-2 text-xs text-slate-500">
                    Selesaikan antrean {{ $aktif->kode_antrean }} terlebih dahulu.
                </p>
            @elseif ($menunggu->isEmpty())
                <x-button type="button" size="lg" class="w-full sm:w-auto sm:min-w-72" disabled>
                    Panggil Berikutnya
                </x-button>
            @else
                <x-button type="button"
                          size="lg"
                          class="w-full sm:w-auto sm:min-w-72"
                          x-on:click="$dispatch('konfirmasi', { aksi: 'panggil_berikutnya', id: null })">
                    Panggil Berikutnya
                </x-button>
            @endif
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="rounded-lg border border-slate-200 bg-white p-3 text-center">
            <p class="text-2xl font-bold text-slate-800">{{ $rekap['menunggu'] }}</p>
            <p class="text-xs text-slate-500">Menunggu</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 text-center">
            <p class="text-2xl font-bold text-success">{{ $rekap['selesai'] }}</p>
            <p class="text-xs text-slate-500">Selesai</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 text-center">
            <p class="text-2xl font-bold text-danger">{{ $rekap['tidak_hadir'] }}</p>
            <p class="text-xs text-slate-500">Tidak Hadir</p>
        </div>
    </div>

    {{-- Daftar menunggu --}}
    <div>
        <h2 class="mb-2 text-sm font-semibold text-slate-700">Antrean Menunggu</h2>

        @if ($menunggu->isEmpty())
            <x-empty-state message="Belum ada pasien menunggu." />
        @else
            <ul class="divide-y divide-slate-100 overflow-hidden rounded-lg border border-slate-200 bg-white">
                @foreach ($menunggu as $baris)
                    <li class="flex items-center gap-3 px-4 py-3">
                        <span class="w-20 font-mono text-lg font-bold text-slate-800">{{ $baris->kode_antrean }}</span>

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-medium text-slate-800">
                                {{ $baris->pasien?->user?->name ?? 'Pasien' }}
                            </span>
                            <span class="block text-xs text-slate-500">
                                Masuk {{ strtoupper($baris->jam_daftar->format('H:i')) }} WIB · {{ $baris->poli?->nama_poli }}
                            </span>
                        </span>

                        <span class="flex items-center gap-2">
                            <x-status-badge :status="$baris->status->value" />

                            @if (! $aktif)
                                <x-button type="button" size="sm"
                                          x-on:click="$dispatch('konfirmasi', { aksi: 'panggil', id: {{ $baris->id }} })">
                                    Panggil
                                </x-button>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Riwayat hari ini --}}
    <div>
        <h2 class="mb-2 text-sm font-semibold text-slate-700">Riwayat Hari Ini</h2>

        @if ($riwayat->isEmpty())
            <x-empty-state message="Belum ada pasien yang dilayani hari ini." />
        @else
            <ul class="divide-y divide-slate-100 overflow-hidden rounded-lg border border-slate-200 bg-white">
                @foreach ($riwayat as $baris)
                    <li class="flex items-center gap-3 px-4 py-3">
                        <span class="w-20 font-mono text-base font-bold text-slate-700">{{ $baris->kode_antrean }}</span>

                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm text-slate-700">
                                {{ $baris->pasien?->user?->name ?? 'Pasien' }}
                            </span>
                            <span class="block text-xs text-slate-500">
                                @if ($baris->jam_selesai)
                                    Selesai {{ strtoupper($baris->jam_selesai->format('H:i')) }} WIB
                                @else
                                    Masuk {{ strtoupper($baris->jam_daftar->format('H:i')) }} WIB
                                @endif
                            </span>
                        </span>

                        <span class="flex items-center gap-2">
                            <x-status-badge :status="$baris->status->value" />

                            @if ($baris->status->value === 'tidak_hadir' && ! $aktif)
                                <x-button type="button" size="sm" variant="secondary"
                                          x-on:click="$dispatch('konfirmasi', { aksi: 'panggil_ulang', id: {{ $baris->id }} })">
                                    Panggil Ulang
                                </x-button>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Modal konfirmasi (project.md Bab 7.7) --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none">
        <div class="absolute inset-0 bg-slate-900/50" x-on:click="batal"></div>

        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl"
             x-transition:enter="transition duration-150 ease-out"
             x-transition:enter-start="scale-95 opacity-0"
             x-transition:enter-end="scale-100 opacity-100">
            <h3 class="text-base font-semibold text-slate-900">
                <template x-if="aksi === 'selesai'">Selesaikan antrean ini?</template>
                <template x-if="aksi === 'panggil_berikutnya'">Panggil nomor berikutnya?</template>
                <template x-if="aksi === 'panggil'">Panggil nomor ini sekarang?</template>
                <template x-if="aksi === 'panggil_ulang'">Panggil ulang pasien ini?</template>
                <template x-if="aksi === 'tidak_hadir'">Tandai pasien tidak hadir?</template>
            </h3>

            <p class="mt-2 text-sm text-slate-600">
                <template x-if="aksi === 'selesai'">
                    <span>Status antrean akan berubah menjadi <strong>Selesai</strong> dan pasien berikutnya siap dipanggil.</span>
                </template>
                <template x-if="aksi === 'panggil_berikutnya' || aksi === 'panggil'">
                    <span>Nomor akan dipanggil pada papan antrean dan status berubah menjadi <strong>Dipanggil</strong>.</span>
                </template>
                <template x-if="aksi === 'panggil_ulang'">
                    <span>Status akan kembali menjadi <strong>Dipanggil</strong>.</span>
                </template>
                <template x-if="aksi === 'tidak_hadir'">
                    <span>Status antrean akan berubah menjadi <strong>Tidak Hadir</strong>. Aksi ini tidak dapat dibatalkan.</span>
                </template>
            </p>

            <div class="mt-5 flex justify-end gap-3">
                <x-button type="button" variant="secondary" x-on:click="batal">Batal</x-button>
                <x-button type="button" x-on:click="konfirmasi">Ya, Lanjutkan</x-button>
            </div>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</div>
