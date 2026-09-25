<x-layouts.admin :title="'Ubah Pasien'">
    <div class="mx-auto max-w-3xl space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Ubah Data Pasien</h1>
                <p class="mt-1 text-sm text-slate-500">
                    No. RM <span class="font-mono font-semibold text-primary">{{ $pasien->no_rm }}</span>
                </p>
            </div>
            <x-button variant="ghost" onclick="location.href='{{ route('admin.pasien.index') }}'">Kembali</x-button>
        </div>

        <x-flash />

        <form method="POST" action="{{ route('admin.pasien.update', $pasien) }}">
            @csrf
            @method('PUT')

            <x-card>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-500">Identitas</div>

                    <div>
                        <x-input-label for="name" value="Nama Lengkap" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $pasien->user?->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email (akun)" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $pasien->user?->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="nik" value="NIK (16 digit)" />
                        <x-text-input id="nik" name="nik" type="text" class="mt-1 block w-full" :value="old('nik', $pasien->nik)" maxlength="16" required />
                        <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="tanggal_lahir" value="Tanggal Lahir" />
                        <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date" class="mt-1 block w-full" :value="old('tanggal_lahir', optional($pasien->tanggal_lahir)->format('Y-m-d'))" required />
                        <x-input-error :messages="$errors->get('tanggal_lahir')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="jenis_kelamin" value="Jenis Kelamin" />
                        <select id="jenis_kelamin" name="jenis_kelamin" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" required>
                            @foreach (\App\Enums\JenisKelamin::cases() as $jenis)
                                <option value="{{ $jenis->value }}" @selected(old('jenis_kelamin', $pasien->jenis_kelamin?->value) === $jenis->value)>{{ $jenis->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="golongan_darah" value="Golongan Darah" />
                        <select id="golongan_darah" name="golongan_darah" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary">
                            <option value="">— Tidak diketahui —</option>
                            @foreach (\App\Enums\GolonganDarah::cases() as $golongan)
                                <option value="{{ $golongan->value }}" @selected(old('golongan_darah', $pasien->golongan_darah?->value) === $golongan->value)>{{ $golongan->value }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('golongan_darah')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="no_telp" value="No. Telepon" />
                        <x-text-input id="no_telp" name="no_telp" type="text" class="mt-1 block w-full" :value="old('no_telp', $pasien->no_telp)" required />
                        <x-input-error :messages="$errors->get('no_telp')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-2">
                        <x-input-label for="alamat" value="Alamat" />
                        <textarea id="alamat" name="alamat" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" required>{{ old('alamat', $pasien->alamat) }}</textarea>
                        <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                    </div>

                    <div class="sm:col-span-2 border-b border-slate-100 pb-2 pt-2 text-sm font-semibold text-slate-500">Akun pengguna</div>

                    <div>
                        <x-input-label for="phone" value="No. Telepon Akun" />
                        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $pasien->user?->phone)" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>
            </x-card>

            <div class="mt-4 flex gap-2">
                <x-button type="submit">Simpan Perubahan</x-button>
                <x-button variant="secondary" type="button" onclick="location.href='{{ route('admin.pasien.index') }}'">Batal</x-button>
            </div>
        </form>
    </div>
</x-layouts.admin>
