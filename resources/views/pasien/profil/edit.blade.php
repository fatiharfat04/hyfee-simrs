<x-layouts.pasien :title="'Data Diri'">
    <div class="space-y-4">
        <x-flash />

        <div>
            <h1 class="text-lg font-semibold">Lengkapi Data Diri</h1>
            <p class="text-sm text-slate-500">Data ini menjadi rekam medis awal Anda.</p>
        </div>

        @if (! $pasien)
            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800">
                Data diri Anda belum lengkap. Isi formulir di bawah untuk dapat mengambil antrean.
            </div>
        @endif

        <form method="POST" action="{{ route('pasien.profil.update') }}">
            @csrf
            @method('PUT')

            <x-card>
                <div class="grid gap-4">
                    <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">
                        Nama & email akun dapat diubah pada menu
                        <a href="{{ route('profile.edit') }}" class="font-semibold text-primary underline">Profil</a>.
                    </div>

                    <div>
                        <x-input-label for="nik" value="NIK (16 digit)" />
                        <x-text-input id="nik" name="nik" type="text" class="mt-1 block w-full" :value="old('nik', $pasien?->nik)" maxlength="16" placeholder="3201234567890001" required />
                        <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tanggal_lahir" value="Tanggal Lahir" />
                            <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date" class="mt-1 block w-full" :value="old('tanggal_lahir', optional($pasien?->tanggal_lahir)->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('tanggal_lahir')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="jenis_kelamin" value="Jenis Kelamin" />
                            <select id="jenis_kelamin" name="jenis_kelamin" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" required>
                                @foreach (\App\Enums\JenisKelamin::cases() as $jenis)
                                    <option value="{{ $jenis->value }}" @selected(old('jenis_kelamin', $pasien?->jenis_kelamin?->value) === $jenis->value)>{{ $jenis->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="no_telp" value="No. Telepon" />
                            <x-text-input id="no_telp" name="no_telp" type="text" class="mt-1 block w-full" :value="old('no_telp', $pasien?->no_telp ?? auth()->user()->phone)" required />
                            <x-input-error :messages="$errors->get('no_telp')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="golongan_darah" value="Golongan Darah" />
                            <select id="golongan_darah" name="golongan_darah" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary">
                                <option value="">— Tidak diketahui —</option>
                                @foreach (\App\Enums\GolonganDarah::cases() as $golongan)
                                    <option value="{{ $golongan->value }}" @selected(old('golongan_darah', $pasien?->golongan_darah?->value) === $golongan->value)>{{ $golongan->value }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('golongan_darah')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="alamat" value="Alamat" />
                        <textarea id="alamat" name="alamat" rows="3" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" required>{{ old('alamat', $pasien?->alamat) }}</textarea>
                        <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                    </div>
                </div>
            </x-card>

            <div class="mt-4">
                <x-button size="lg" type="submit" class="w-full">Simpan Data Diri</x-button>
            </div>
        </form>
    </div>
</x-layouts.pasien>
