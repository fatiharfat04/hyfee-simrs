<x-layouts.admin :title="'Ubah Poli'">
    <div class="mx-auto max-w-2xl space-y-4">
        <div>
            <h1 class="text-xl font-semibold">Ubah Poli</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $poli->kode_poli }} — {{ $poli->nama_poli }}</p>
        </div>

        <x-flash />

        <form method="POST" action="{{ route('admin.poli.update', $poli) }}">
            @csrf
            @method('PUT')

            <x-card>
                @include('admin.poli._form', ['poli' => $poli])
            </x-card>

            <div class="mt-4 flex gap-2">
                <x-button type="submit">Simpan Perubahan</x-button>
                <x-button variant="secondary" type="button" onclick="location.href='{{ route('admin.poli.index') }}'">Batal</x-button>
            </div>
        </form>
    </div>
</x-layouts.admin>
