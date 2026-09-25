<x-layouts.admin :title="'Ubah Dokter'">
    <div class="mx-auto max-w-3xl space-y-4">
        <div>
            <h1 class="text-xl font-semibold">Ubah Dokter</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $dokter->namaLengkap() }} — {{ $dokter->no_sip }}</p>
        </div>

        <x-flash />

        <form method="POST" action="{{ route('admin.dokter.update', $dokter) }}">
            @csrf
            @method('PUT')

            <x-card>
                @include('admin.dokter._form', ['dokter' => $dokter])
            </x-card>

            <div class="mt-4 flex gap-2">
                <x-button type="submit">Simpan Perubahan</x-button>
                <x-button variant="secondary" type="button" onclick="location.href='{{ route('admin.dokter.index') }}'">Batal</x-button>
            </div>
        </form>
    </div>
</x-layouts.admin>
