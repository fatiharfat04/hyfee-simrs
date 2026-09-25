<x-layouts.admin :title="'Poli'">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Data Poli</h1>
                <p class="mt-1 text-sm text-slate-500">Kelola poli, prefix antrean, dan lokasi ruang.</p>
            </div>
            <x-button onclick="location.href='{{ route('admin.poli.create') }}'">+ Tambah Poli</x-button>
        </div>

        <x-flash />

        <form method="GET" action="{{ route('admin.poli.index') }}" class="flex gap-2">
            <input type="text" name="q" value="{{ $keyword }}" placeholder="Cari kode/nama poli..."
                   class="w-full max-w-xs rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary">
            <x-button variant="secondary" size="sm" type="submit">Cari</x-button>
            @if ($keyword !== '')
                <x-button variant="ghost" size="sm" onclick="location.href='{{ route('admin.poli.index') }}'">Reset</x-button>
            @endif
        </form>

        @if ($polis->isEmpty())
            <x-empty-state message="Belum ada data poli">
                <x-slot:action>
                    <x-button size="sm" onclick="location.href='{{ route('admin.poli.create') }}'">Tambah Poli</x-button>
                </x-slot:action>
            </x-empty-state>
        @else
            <x-table :headers="['Kode', 'Nama Poli', 'Prefix', 'Lokasi Ruang', 'Status', 'Aksi']">
                @foreach ($polis as $poli)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-slate-700">{{ $poli->kode_poli }}</td>
                        <td class="px-4 py-3 font-medium">{{ $poli->nama_poli }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-primary/10 text-sm font-bold text-primary">{{ $poli->prefix_antrean }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $poli->lokasi_ruang }}</td>
                        <td class="px-4 py-3">
                            @if ($poli->is_active)
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <x-button variant="secondary" size="sm" onclick="location.href='{{ route('admin.poli.show', $poli) }}'">Detail</x-button>
                                <x-button variant="secondary" size="sm" onclick="location.href='{{ route('admin.poli.edit', $poli) }}'">Ubah</x-button>
                                <form method="POST" action="{{ route('admin.poli.destroy', $poli) }}"
                                      onsubmit="return confirm('Hapus poli {{ $poli->nama_poli }}? Tindakan ini tidak bisa dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button variant="danger" size="sm" type="submit">Hapus</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <div class="mt-4">{{ $polis->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
