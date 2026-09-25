<x-layouts.admin :title="'Ubah Jadwal'">
    <div class="mx-auto max-w-2xl space-y-4">
        <div>
            <h1 class="text-xl font-semibold">Ubah Jadwal Praktik</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $jadwal->dokter->namaLengkap() }}</p>
        </div>

        <x-flash />

        <form method="POST" action="{{ route('admin.jadwal.update', $jadwal) }}">
            @csrf
            @method('PUT')

            <x-card>
                @include('admin.jadwal._form', [
                    'jadwal' => $jadwal,
                    'dokters' => $dokters,
                    'dokterTerpilih' => $jadwal->dokter_id,
                ])
            </x-card>

            <div class="mt-4 flex gap-2">
                <x-button type="submit">Simpan Perubahan</x-button>
                <x-button variant="secondary" type="button" onclick="location.href='{{ route('admin.jadwal.index') }}'">Batal</x-button>
            </div>
        </form>
    </div>
</x-layouts.admin>
