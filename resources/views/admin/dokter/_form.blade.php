<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2 border-b border-slate-100 pb-2 text-sm font-semibold text-slate-500">Akun pengguna</div>

    <div>
        <x-input-label for="name" value="Nama Dokter" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $dokter?->user?->name)" placeholder="Andini Pratiwi" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $dokter?->user?->email)" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="phone" value="No. Telepon" />
        <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $dokter?->user?->phone)" placeholder="0812..." />
        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password" value="{{ $dokter->exists ? 'Password Baru (kosongkan jika tetap)' : 'Password' }}" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" placeholder="Minimal 8 karakter" :required="! $dokter->exists" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="password_confirmation" value="Konfirmasi Password" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="! $dokter->exists" />
    </div>

    <div class="sm:col-span-2 border-b border-slate-100 pb-2 pt-2 text-sm font-semibold text-slate-500">Data keprofesian</div>

    <div>
        <x-input-label for="poli_id" value="Poli" />
        <select id="poli_id" name="poli_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" required>
            <option value="">— Pilih poli —</option>
            @foreach ($polis as $poli)
                <option value="{{ $poli->id }}" @selected((string) old('poli_id', $dokter?->poli_id) === (string) $poli->id)>{{ $poli->nama_poli }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('poli_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="no_sip" value="No. SIP (Surat Izin Praktik)" />
        <x-text-input id="no_sip" name="no_sip" type="text" class="mt-1 block w-full" :value="old('no_sip', $dokter->no_sip)" placeholder="SIP-2026-0001" required />
        <x-input-error :messages="$errors->get('no_sip')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="gelar_depan" value="Gelar Depan" />
        <x-text-input id="gelar_depan" name="gelar_depan" type="text" class="mt-1 block w-full" :value="old('gelar_depan', $dokter->gelar_depan)" placeholder="dr." />
        <x-input-error :messages="$errors->get('gelar_depan')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="gelar_belakang" value="Gelar Belakang" />
        <x-text-input id="gelar_belakang" name="gelar_belakang" type="text" class="mt-1 block w-full" :value="old('gelar_belakang', $dokter->gelar_belakang)" placeholder="Sp.A" />
        <x-input-error :messages="$errors->get('gelar_belakang')" class="mt-2" />
    </div>
</div>

<label class="mt-4 flex items-center gap-2 text-sm text-slate-700">
    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-primary focus:ring-primary"
           @checked(old('is_active', $dokter->exists ? $dokter->is_active : true))>
    Dokter aktif (dapat melayani antrean)
</label>
