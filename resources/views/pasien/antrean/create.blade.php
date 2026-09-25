<x-layouts.pasien :title="'Daftar Antrean'">
    <div class="space-y-4">
        <x-flash />

        @if ($antreanAktif)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-800">Anda sudah terdaftar hari ini</p>
                <p class="mt-1 text-sm text-amber-700">
                    Nomor antrean Anda
                    <span class="font-mono text-lg font-bold text-amber-900">{{ $antreanAktif->kode_antrean }}</span>
                    di {{ $antreanAktif->poli?->nama_poli }}.
                </p>
                <a href="{{ route('pasien.antrean.mine') }}" class="mt-2 inline-block text-sm font-semibold text-primary underline">Lihat Antrean Saya</a>
            </div>
        @endif

        <div>
            <h1 class="text-lg font-semibold">Daftar Antrean Rawat Jalan</h1>
            <p class="text-sm text-slate-500">
                {{ now()->translatedFormat('l, d F Y') }} — pilih poli, lalu dokter yang berjadwal hari ini.
            </p>
        </div>

        @if ($dokters->isEmpty())
            <x-empty-state message="Tidak ada jadwal praktik dokter hari ini. Silakan kembali besok." />
        @else
            <form method="POST" action="{{ route('pasien.antrean.store') }}"
                  x-data="{ poliTerpilih: {{ old('poli_id', $dokters->first()?->poli_id) }} }">
                @csrf

                {{-- Pilih poli --}}
                <fieldset class="space-y-2">
                    <legend class="text-sm font-semibold text-slate-700">1. Pilih Poli</legend>

                    <div class="grid grid-cols-2 gap-2">
                        @foreach ($polis as $poli)
                            @if ($dokters->where('poli_id', $poli->id)->isNotEmpty())
                                <button type="button"
                                        @click="poliTerpilih = {{ $poli->id }}"
                                        :class="poliTerpilih === {{ $poli->id }}
                                            ? 'border-primary bg-primary text-white'
                                            : 'border-slate-200 bg-white text-slate-700'"
                                        class="rounded-xl border-2 p-3 text-left shadow-sm transition">
                                    <span class="block text-sm font-semibold">{{ $poli->nama_poli }}</span>
                                    <span class="mt-0.5 block text-xs opacity-75">{{ $poli->lokasi_ruang }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <input type="hidden" name="poli_id" :value="poliTerpilih">
                    <x-input-error :messages="$errors->get('poli_id')" class="mt-1" />
                </fieldset>

                {{-- Pilih dokter --}}
                <fieldset class="mt-5 space-y-2">
                    <legend class="text-sm font-semibold text-slate-700">2. Pilih Dokter</legend>

                    <div class="space-y-2">
                        @foreach ($dokters as $dokter)
                            @php $sisa = max(0, $dokter->jadwalDokters->sum(fn ($j) => $j->kuota - ($terpakai[$j->id] ?? 0))); @endphp
                            <label x-show="poliTerpilih === {{ $dokter->poli_id }}"
                                   x-cloak
                                   class="flex cursor-pointer items-start gap-3 rounded-xl border-2 border-slate-200 bg-white p-4 shadow-sm transition has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                <input type="radio" name="dokter_id" value="{{ $dokter->id }}"
                                       class="mt-1 h-4 w-4 border-slate-300 text-primary focus:ring-primary"
                                       @checked((string) old('dokter_id') === (string) $dokter->id)>
                                <span class="flex-1">
                                    <span class="block text-sm font-semibold text-slate-800">{{ $dokter->namaLengkap() }}</span>
                                    <span class="mt-1 block text-xs text-slate-500">
                                        Praktik:
                                        @foreach ($dokter->jadwalDokters as $i => $jadwal)
                                            {{ $i > 0 ? ', ' : '' }}{{ $jadwal->jam_mulai->format('H:i') }}–{{ $jadwal->jam_selesai->format('H:i') }}
                                        @endforeach
                                    </span>
                                    <span class="mt-1 block text-xs {{ $sisa > 0 ? 'text-green-700' : 'text-red-600' }}">
                                        @if ($sisa > 0)
                                            Sisa kuota {{ $sisa }} pasien
                                        @else
                                            Kuota penuh
                                        @endif
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <x-input-error :messages="$errors->get('dokter_id')" class="mt-1" />
                </fieldset>

                <div class="mt-6">
                    <x-button size="lg" type="submit" class="w-full">Ambil Nomor Antrean</x-button>
                    <p class="mt-2 text-center text-xs text-slate-500">
                        Pastikan data diri Anda sudah lengkap sebelum mendaftar.
                    </p>
                </div>
            </form>
        @endif
    </div>

    @once
        <style>[x-cloak]{display:none !important;}</style>
    @endonce
</x-layouts.pasien>
