<x-layouts.pasien :title="'Antrean Saya'">
    <div class="space-y-4">
        <x-flash />

        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold">Antrean Saya</h1>
            <a href="{{ route('pasien.antrean.create') }}" class="rounded-lg bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm">+ Daftar</a>
        </div>

        @if ($antreans->isEmpty())
            <x-empty-state message="Anda belum memiliki antrean">
                <x-slot:action>
                    <x-button size="sm" onclick="location.href='{{ route('pasien.antrean.create') }}'">Ambil Antrean</x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            {{-- Sorotan antrean hari ini --}}
            @if ($antreanHariIni)
                @php
                    $langkah = [\App\Enums\AntreanStatus::MENUNGGU, \App\Enums\AntreanStatus::DIPANGGIL, \App\Enums\AntreanStatus::DILAYANI, \App\Enums\AntreanStatus::SELESAI];
                    $posisi = array_search($antreanHariIni->status, $langkah, true);
                    $selesai = $antreanHariIni->status === \App\Enums\AntreanStatus::SELESAI;
                    $dibatalkan = in_array($antreanHariIni->status, [\App\Enums\AntreanStatus::BATAL, \App\Enums\AntreanStatus::TIDAK_HADIR], true);
                @endphp
                <div class="overflow-hidden rounded-2xl bg-primary text-white shadow">
                    <div class="flex items-start justify-between p-5 pb-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-white/70">Nomor Antrean Anda</p>
                            <p class="mt-1 font-mono text-6xl font-bold leading-none">{{ $antreanHariIni->kode_antrean }}</p>
                            <p class="mt-2 text-sm text-white/80">
                                {{ $antreanHariIni->poli?->nama_poli }} · {{ $antreanHariIni->dokter?->namaLengkap() }}
                            </p>
                            <p class="text-xs text-white/70">{{ $antreanHariIni->poli?->lokasi_ruang }}</p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex rounded-full bg-white/20 px-3 py-1 text-sm font-semibold">
                                {{ $antreanHariIni->status->label() }}
                            </span>
                        </div>
                    </div>

                    @unless ($dibatalkan)
                        <div class="border-t border-white/15 px-5 py-4">
                            <ol class="flex items-center justify-between text-[11px] font-medium">
                                @foreach ($langkah as $i => $tahap)
                                    <li class="flex flex-1 flex-col items-center gap-1">
                                        <span @class([
                                            'flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold',
                                            'bg-white text-primary' => $selesai || $i <= $posisi,
                                            'bg-white/25 text-white/80' => ! $selesai && $i > $posisi,
                                        ])>
                                            @if ($selesai || $i < $posisi)
                                                &#10003;
                                            @else
                                                {{ $i + 1 }}
                                            @endif
                                        </span>
                                        <span class="text-center leading-tight {{ $selesai || $i <= $posisi ? 'text-white' : 'text-white/60' }}">{{ $tahap->label() }}</span>
                                    </li>
                                @endforeach
                            </ol>

                            @if ($antreanHariIni->status === \App\Enums\AntreanStatus::MENUNGGU)
                                <p class="mt-3 text-center text-xs text-white/80">
                                    Silakan menunggu di ruang tunggu. Nomor Anda akan dipanggil di papan antrean.
                                </p>
                            @elseif ($antreanHariIni->status === \App\Enums\AntreanStatus::DIPANGGIL)
                                <p class="mt-3 animate-pulse text-center text-sm font-semibold text-white">
                                    Nomor Anda sedang dipanggil — silakan menuju {{ $antreanHariIni->poli?->lokasi_ruang }}
                                </p>
                            @endif
                        </div>
                    @endunless
                </div>

                @if ($antreanHariIni->status === \App\Enums\AntreanStatus::MENUNGGU)
                    <form method="POST" action="{{ route('pasien.antrean.cancel', $antreanHariIni) }}"
                          onsubmit="return confirm('Batalkan antrean {{ $antreanHariIni->kode_antrean }}?');">
                        @csrf
                        <button type="submit" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-600">
                            Batalkan Antrean Ini
                        </button>
                    </form>
                @endif
            @endif

            <h2 class="mt-6 text-sm font-semibold text-slate-700">Riwayat antrean</h2>

            <div class="space-y-2">
                @foreach ($antreans as $antrean)
                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-mono text-lg font-bold text-slate-800">{{ $antrean->kode_antrean }}</p>
                                <p class="text-xs text-slate-500">{{ $antrean->tanggal_antrean->translatedFormat('d M Y') }}</p>
                            </div>
                            <x-status-badge :status="$antrean->status->value" />
                        </div>
                        <div class="mt-2 border-t border-slate-100 pt-2 text-xs text-slate-600">
                            <p><span class="font-medium">{{ $antrean->poli?->nama_poli }}</span> · {{ $antrean->dokter?->namaLengkap() }}</p>
                            <p class="mt-0.5">Terdaftar {{ optional($antrean->jam_daftar)->format('H:i') }} WIB</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">{{ $antreans->links() }}</div>
        @endif
    </div>
</x-layouts.pasien>
