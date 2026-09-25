<x-layouts.admin :title="'Dokter'">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Data Dokter</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola dokter, penempatan poli, dan jadwal praktik.</p>
            </div>
            <x-button onclick="location.href='{{ route('admin.dokter.create') }}'">+ Tambah Dokter</x-button>
        </div>

        <x-flash />

        <form method="GET" action="{{ route('admin.dokter.index') }}" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ $keyword }}" placeholder="Cari nama / No. SIP..."
                   class="w-full max-w-xs rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary">
            <select name="poli_id" class="rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary">
                <option value="0">Semua poli</option>
                @foreach ($polis as $poli)
                    <option value="{{ $poli->id }}" @selected($poliTerpilih === $poli->id)>{{ $poli->nama_poli }}</option>
                @endforeach
            </select>
            <x-button variant="secondary" size="sm" type="submit">Filter</x-button>
            <x-button variant="ghost" size="sm" onclick="location.href='{{ route('admin.dokter.index') }}'">Reset</x-button>
        </form>

        @if ($dokters->isEmpty())
            <x-empty-state message="Belum ada data dokter" />
        @else
            <x-table :headers="['Nama Dokter', 'No. SIP', 'Poli', 'Jadwal', 'Status', 'Aksi']">
                @foreach ($dokters as $dokter)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $dokter->namaLengkap() }}</p>
                            <p class="text-xs text-slate-500">{{ $dokter->user?->email }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-slate-700">{{ $dokter->no_sip }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $dokter->poli?->nama_poli }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $dokter->jadwalDokters()->where('is_active', true)->count() }} sesi</td>
                        <td class="px-4 py-3">
                            @if ($dokter->is_active)
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <x-button variant="secondary" size="sm" onclick="location.href='{{ route('admin.dokter.show', $dokter) }}'">Detail</x-button>
                                <x-button variant="secondary" size="sm" onclick="location.href='{{ route('admin.dokter.edit', $dokter) }}'">Ubah</x-button>
                                <form method="POST" action="{{ route('admin.dokter.destroy', $dokter) }}"
                                      onsubmit="return confirm('Hapus dokter {{ $dokter->namaLengkap() }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" size="sm" type="submit">Hapus</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <div class="mt-4">{{ $dokters->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
