<x-layouts.admin :title="'Laporan'">
    <div class="space-y-4">
        <div>
            <h1 class="text-xl font-semibold">Laporan &amp; Statistik</h1>
            <p class="mt-1 text-sm text-slate-500">Jumlah pasien, rata-rata waktu tunggu, dan rata-rata waktu layanan per poli.</p>
        </div>

        <x-flash />

        {{-- Filter periode + tombol ekspor (project.md Prompt 8) --}}
        <x-card>
            <form method="GET" action="{{ route('admin.laporan.index') }}" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="periode" class="mb-1 block text-sm font-medium text-slate-700">Periode</label>
                    <select id="periode" name="periode" class="rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary">
                        <option value="hari" @selected($periode === 'hari')>Harian</option>
                        <option value="bulan" @selected($periode === 'bulan')>Bulanan</option>
                    </select>
                </div>

                <div>
                    <label for="tanggal" class="mb-1 block text-sm font-medium text-slate-700">Tanggal acuan</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ $tanggal }}"
                           class="rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary">
                </div>

                <x-button type="submit">Terapkan</x-button>

                <div class="ml-auto flex flex-wrap gap-2">
                    <x-button
                        variant="secondary"
                        type="button"
                        onclick="location.href='{{ route('admin.laporan.export', ['periode' => $periode, 'tanggal' => $tanggal, 'format' => 'excel']) }}'">
                        Export Excel
                    </x-button>
                    <x-button
                        variant="danger"
                        type="button"
                        onclick="location.href='{{ route('admin.laporan.export', ['periode' => $periode, 'tanggal' => $tanggal, 'format' => 'pdf']) }}'">
                        Export PDF
                    </x-button>
                </div>
            </form>

            <p class="mt-3 text-xs text-slate-500">
                Rentang laporan: <span class="font-medium text-slate-700">{{ $labelRentang }}</span>
                @if ($periode === 'bulan')
                    — seluruh bulan dari tanggal acuan.
                @endif
            </p>
        </x-card>

        {{-- Ringkasan atas --}}
        <div class="grid gap-3 sm:grid-cols-3">
            <x-card>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Pasien</p>
                <p class="mt-1 text-2xl font-bold text-neutral-text">{{ number_format($total['jumlah']) }}</p>
                <p class="mt-1 text-xs text-slate-500">antrean terdaftar</p>
            </x-card>
            <x-card>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Rata-rata Tunggu</p>
                <p class="mt-1 text-2xl font-bold text-warning">{{ $total['rata_tunggu'] }}</p>
                <p class="mt-1 text-xs text-slate-500">jam daftar → dipanggil</p>
            </x-card>
            <x-card>
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Rata-rata Layanan</p>
                <p class="mt-1 text-2xl font-bold text-success">{{ $total['rata_layanan'] }}</p>
                <p class="mt-1 text-xs text-slate-500">dipanggil → selesai</p>
            </x-card>
        </div>

        {{-- Tabel rekap, memakai komponen reusable x-table (project.md Bab 7.6) --}}
        @if ($total['jumlah'] === 0)
            <x-empty-state message="Belum ada antrean pada rentang ini" />
        @else
            <x-table :headers="['Kode', 'Poli', 'Lokasi Ruang', 'Jumlah Pasien', 'Rata-rata Tunggu', 'Rata-rata Layanan']">
                @foreach ($rows as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-slate-700">{{ $row['kode_poli'] }}</td>
                        <td class="px-4 py-3 font-medium">{{ $row['nama_poli'] }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $row['lokasi_ruang'] }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ number_format($row['jumlah']) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $row['rata_tunggu_teks'] }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $row['rata_layanan_teks'] }}</td>
                    </tr>
                @endforeach
                <tr class="bg-slate-50 font-semibold">
                    <td class="px-4 py-3" colspan="3">Total / Seluruh Poli</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ number_format($total['jumlah']) }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ $total['rata_tunggu'] }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ $total['rata_layanan'] }}</td>
                </tr>
            </x-table>
        @endif
    </div>
</x-layouts.admin>
