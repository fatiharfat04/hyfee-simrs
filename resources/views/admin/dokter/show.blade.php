<x-layouts.admin :title="'Detail Dokter'">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">{{ $dokter->namaLengkap() }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $dokter->poli?->nama_poli }} · No. SIP {{ $dokter->no_sip }} · Antrean hari ini: {{ $antreanHariIni }}
                </p>
            </div>
            <div class="flex gap-2">
                <x-button variant="secondary" onclick="location.href='{{ route('admin.jadwal.create', ['dokter_id' => $dokter->id]) }}'">+ Jadwal</x-button>
                <x-button variant="secondary" onclick="location.href='{{ route('admin.dokter.edit', $dokter) }}'">Ubah</x-button>
                <x-button variant="ghost" onclick="location.href='{{ route('admin.dokter.index') }}'">Kembali</x-button>
            </div>
        </div>

        <x-flash />

        <section class="space-y-2">
            <h2 class="text-base font-semibold">Jadwal praktik</h2>
            @if ($dokter->jadwalDokters->isEmpty())
                <x-empty-state message="Belum ada jadwal praktik" />
            @else
                <x-table :headers="['Hari', 'Jam', 'Kuota', 'Status', 'Aksi']">
                    @foreach ($dokter->jadwalDokters as $jadwal)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $jadwal->hari->label() }}</td>
                            <td class="px-4 py-3">{{ $jadwal->jam_mulai->format('H:i') }} – {{ $jadwal->jam_selesai->format('H:i') }}</td>
                            <td class="px-4 py-3">{{ $jadwal->kuota }} pasien</td>
                            <td class="px-4 py-3">
                                @if ($jadwal->is_active)
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-button variant="secondary" size="sm" onclick="location.href='{{ route('admin.jadwal.edit', $jadwal) }}'">Ubah</x-button>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </section>
    </div>
</x-layouts.admin>
