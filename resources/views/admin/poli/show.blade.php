<x-layouts.admin :title="'Detail Poli'">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">{{ $poli->nama_poli }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $poli->kode_poli }} · Prefix {{ $poli->prefix_antrean }} · {{ $poli->lokasi_ruang }}</p>
            </div>
            <div class="flex gap-2">
                <x-button variant="secondary" onclick="location.href='{{ route('admin.poli.edit', $poli) }}'">Ubah</x-button>
                <x-button variant="ghost" onclick="location.href='{{ route('admin.poli.index') }}'">Kembali</x-button>
            </div>
        </div>

        <x-flash />

        <section class="space-y-2">
            <h2 class="text-base font-semibold">Dokter di poli ini</h2>
            @if ($poli->dokters->isEmpty())
                <x-empty-state message="Belum ada dokter di poli ini" />
            @else
                <x-table :headers="['Nama Dokter', 'No. SIP', 'Status']">
                    @foreach ($poli->dokters as $dokter)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $dokter->namaLengkap() }}</td>
                            <td class="px-4 py-3 font-mono text-slate-600">{{ $dokter->no_sip }}</td>
                            <td class="px-4 py-3">
                                @if ($dokter->is_active)
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </section>

        <section class="space-y-2">
            <h2 class="text-base font-semibold">20 antrean terakhir</h2>
            @if ($poli->antreans->isEmpty())
                <x-empty-state message="Belum ada antrean di poli ini" />
            @else
                <x-table :headers="['Kode', 'Tanggal', 'Pasien', 'Status']">
                    @foreach ($poli->antreans as $antrean)
                        <tr>
                            <td class="px-4 py-3 font-mono font-semibold">{{ $antrean->kode_antrean }}</td>
                            <td class="px-4 py-3">{{ $antrean->tanggal_antrean->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $antrean->pasien?->user?->name ?? '-' }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$antrean->status->value" /></td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </section>
    </div>
</x-layouts.admin>
