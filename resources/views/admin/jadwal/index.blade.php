<x-layouts.admin :title="'Jadwal Praktik'">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Jadwal Praktik Dokter</h1>
                <p class="mt-1 text-sm text-slate-500">Setiap sesi punya kuota maksimal pasien.</p>
            </div>
            <x-button onclick="location.href='{{ route('admin.jadwal.create') }}'">+ Tambah Jadwal</x-button>
        </div>

        <x-flash />

        <form method="GET" action="{{ route('admin.jadwal.index') }}" class="flex flex-wrap gap-2">
            <select name="dokter_id" class="rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary">
                <option value="0">Semua dokter</option>
                @foreach ($dokters as $dokter)
                    <option value="{{ $dokter->id }}" @selected($dokterTerpilih === $dokter->id)>{{ $dokter->namaLengkap() }}</option>
                @endforeach
            </select>
            <x-button variant="secondary" size="sm" type="submit">Filter</x-button>
            <x-button variant="ghost" size="sm" onclick="location.href='{{ route('admin.jadwal.index') }}'">Reset</x-button>
        </form>

        @if ($jadwals->isEmpty())
            <x-empty-state message="Belum ada jadwal praktik" />
        @else
            <x-table :headers="['Dokter', 'Poli', 'Hari', 'Jam', 'Kuota', 'Status', 'Aksi']">
                @foreach ($jadwals as $jadwal)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium">{{ $jadwal->dokter->namaLengkap() }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $jadwal->dokter->poli?->nama_poli }}</td>
                        <td class="px-4 py-3">{{ $jadwal->hari->label() }}</td>
                        <td class="px-4 py-3">{{ $jadwal->jam_mulai->format('H:i') }} – {{ $jadwal->jam_selesai->format('H:i') }}</td>
                        <td class="px-4 py-3">{{ $jadwal->kuota }}</td>
                        <td class="px-4 py-3">
                            @if ($jadwal->is_active)
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <x-button variant="secondary" size="sm" onclick="location.href='{{ route('admin.jadwal.edit', $jadwal) }}'">Ubah</x-button>
                                <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal) }}"
                                      onsubmit="return confirm('Hapus jadwal {{ $jadwal->dokter->namaLengkap() }} {{ $jadwal->hari->label() }} {{ $jadwal->jam_mulai->format('H:i') }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" size="sm" type="submit">Hapus</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <div class="mt-4">{{ $jadwals->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
