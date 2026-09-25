<x-layouts.admin :title="'Pasien'">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Data Pasien</h1>
                <p class="mt-1 text-sm text-slate-500">Pasien terdaftar otomatis saat pendaftaran antrean. Di sini hanya view & edit.</p>
            </div>
        </div>

        <x-flash />

        <form method="GET" action="{{ route('admin.pasien.index') }}" class="flex gap-2">
            <input type="text" name="q" value="{{ $keyword }}" placeholder="Cari no. RM / NIK / nama..."
                   class="w-full max-w-xs rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary">
            <x-button variant="secondary" size="sm" type="submit">Cari</x-button>
            <x-button variant="ghost" size="sm" onclick="location.href='{{ route('admin.pasien.index') }}'">Reset</x-button>
        </form>

        @if ($pasiens->isEmpty())
            <x-empty-state message="Belum ada data pasien" />
        @else
            <x-table :headers="['No. RM', 'Nama', 'NIK', 'Tgl. Lahir', 'No. Telp', 'Aksi']">
                @foreach ($pasiens as $pasien)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono font-semibold text-primary">{{ $pasien->no_rm }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $pasien->user?->name ?? '—' }}</p>
                            <p class="text-xs text-slate-500">{{ $pasien->jenis_kelamin->label() }} · Gol. darah {{ $pasien->golongan_darah?->value ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 font-mono text-slate-700">{{ $pasien->nik }}</td>
                        <td class="px-4 py-3">{{ $pasien->tanggal_lahir->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $pasien->no_telp }}</td>
                        <td class="px-4 py-3">
                            <x-button variant="secondary" size="sm" onclick="location.href='{{ route('admin.pasien.edit', $pasien) }}'">Ubah</x-button>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <div class="mt-4">{{ $pasiens->links() }}</div>
        @endif
    </div>
</x-layouts.admin>
