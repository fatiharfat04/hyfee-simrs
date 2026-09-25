<div class="grid gap-4 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="dokter_id" value="Dokter" />
        <select id="dokter_id" name="dokter_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" required>
            <option value="">— Pilih dokter —</option>
            @foreach ($dokters as $dokter)
                <option value="{{ $dokter->id }}" @selected((string) old('dokter_id', $jadwal?->dokter_id ?? $dokterTerpilih) === (string) $dokter->id)>
                    {{ $dokter->namaLengkap() }} — {{ $dokter->poli?->nama_poli }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('dokter_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="hari" value="Hari" />
        <select id="hari" name="hari" class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" required>
            <option value="">— Pilih hari —</option>
            @foreach (\App\Enums\Hari::options() as $value => $label)
                <option value="{{ $value }}" @selected(old('hari', $jadwal?->hari?->value) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('hari')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="kuota" value="Kuota (maks. pasien)" />
        <x-text-input id="kuota" name="kuota" type="number" min="1" max="500" class="mt-1 block w-full" :value="old('kuota', $jadwal->exists ? $jadwal->kuota : 30)" required />
        <x-input-error :messages="$errors->get('kuota')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="jam_mulai" value="Jam Mulai" />
        <input id="jam_mulai" name="jam_mulai" type="time" value="{{ old('jam_mulai', $jadwal->exists ? $jadwal->jam_mulai->format('H:i') : '08:00') }}"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" required>
        <x-input-error :messages="$errors->get('jam_mulai')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="jam_selesai" value="Jam Selesai" />
        <input id="jam_selesai" name="jam_selesai" type="time" value="{{ old('jam_selesai', $jadwal->exists ? $jadwal->jam_selesai->format('H:i') : '14:00') }}"
               class="mt-1 block w-full rounded-lg border-slate-300 text-sm focus:border-primary focus:ring-primary" required>
        <x-input-error :messages="$errors->get('jam_selesai')" class="mt-2" />
    </div>
</div>

<label class="mt-4 flex items-center gap-2 text-sm text-slate-700">
    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-primary focus:ring-primary"
           @checked(old('is_active', $jadwal->exists ? $jadwal->is_active : true))>
    Jadwal aktif (terbuka untuk pendaftaran pasien)
</label>
