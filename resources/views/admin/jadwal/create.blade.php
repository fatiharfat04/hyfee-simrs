<x-layouts.admin :title="'Tambah Jadwal'">
    <div class="mx-auto max-w-2xl space-y-4">
        <div>
            <h1 class="text-xl font-semibold">Tambah Jadwal Praktik</h1>
            <p class="mt-1 text-sm text-slate-500">Kombinasi dokter + hari + jam mulai hanya boleh satu kali.</p>
        </div>

        <x-flash />

        <form method="POST" action="{{ route('admin.jadwal.store') }}">
            @csrf
            <x-card>
                @include('admin.jadwal._form', [
                    'jadwal' => new \App\Models\JadwalDokter(),
                    'dokters' => $dokters,
                    'dokterTerpilih' => $dokterTerpilih,
                ])
            </x-card>

            <div class="mt-4 flex gap-2">
                <x-button type="submit">Simpan</x-button>
                <x-button variant="secondary" type="button" onclick="location.href='{{ route('admin.jadwal.index') }}'">Batal</x-button>
            </div>
        </form>
    </div>
</x-layouts.admin>
