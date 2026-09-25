<x-layouts.admin :title="'Tambah Dokter'">
    <div class="mx-auto max-w-3xl space-y-4">
        <div>
            <h1 class="text-xl font-semibold">Tambah Dokter</h1>
            <p class="mt-1 text-sm text-slate-500">Akun baru akan dibuat dengan role <strong>dokter</strong>.</p>
        </div>

        <x-flash />

        <form method="POST" action="{{ route('admin.dokter.store') }}">
            @csrf
            <x-card>
                @include('admin.dokter._form', ['dokter' => new \App\Models\Dokter()])
            </x-card>

            <div class="mt-4 flex gap-2">
                <x-button type="submit">Simpan</x-button>
                <x-button variant="secondary" type="button" onclick="location.href='{{ route('admin.dokter.index') }}'">Batal</x-button>
            </div>
        </form>
    </div>
</x-layouts.admin>
