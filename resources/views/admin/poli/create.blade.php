<x-layouts.admin :title="'Tambah Poli'">
    <div class="mx-auto max-w-2xl space-y-4">
        <div>
            <h1 class="text-xl font-semibold">Tambah Poli</h1>
            <p class="mt-1 text-sm text-slate-500">Pastikan prefix antrean unik agar nomor antrean tidak tertukar.</p>
        </div>

        <x-flash />

        <form method="POST" action="{{ route('admin.poli.store') }}">
            @csrf
            <x-card>
                @include('admin.poli._form', ['poli' => new \App\Models\Poli()])
            </x-card>

            <div class="mt-4 flex gap-2">
                <x-button type="submit">Simpan</x-button>
                <x-button variant="secondary" type="button" onclick="location.href='{{ route('admin.poli.index') }}'">Batal</x-button>
            </div>
        </form>
    </div>
</x-layouts.admin>
