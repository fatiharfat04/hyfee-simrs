<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="kode_poli" value="Kode Poli" />
        <x-text-input id="kode_poli" name="kode_poli" type="text" class="mt-1 block w-full" :value="old('kode_poli', $poli->kode_poli)" placeholder="POL-01" required />
        <x-input-error :messages="$errors->get('kode_poli')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nama_poli" value="Nama Poli" />
        <x-text-input id="nama_poli" name="nama_poli" type="text" class="mt-1 block w-full" :value="old('nama_poli', $poli->nama_poli)" placeholder="Poli Umum" required />
        <x-input-error :messages="$errors->get('nama_poli')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="prefix_antrean" value="Prefix Antrean (1 huruf)" />
        <x-text-input id="prefix_antrean" name="prefix_antrean" type="text" class="mt-1 block w-full uppercase" :value="old('prefix_antrean', $poli->prefix_antrean)" placeholder="A" maxlength="1" required />
        <x-input-error :messages="$errors->get('prefix_antrean')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="lokasi_ruang" value="Lokasi Ruang" />
        <x-text-input id="lokasi_ruang" name="lokasi_ruang" type="text" class="mt-1 block w-full" :value="old('lokasi_ruang', $poli->lokasi_ruang)" placeholder="Lantai 1 Ruang 1" required />
        <x-input-error :messages="$errors->get('lokasi_ruang')" class="mt-2" />
    </div>
</div>

<label class="mt-4 flex items-center gap-2 text-sm text-slate-700">
    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-primary focus:ring-primary"
           @checked(old('is_active', $poli->exists ? $poli->is_active : true))>
    Poli aktif (ditampilkan pada pilihan pendaftaran pasien)
</label>
