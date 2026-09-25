<?php

namespace App\Http\Requests\Admin;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePasienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pasien = $this->route('pasien');

        return [
            // Data identitas
            'nik' => [
                'required', 'string', 'size:16', 'regex:/^[0-9]{16}$/',
                Rule::unique('pasiens', 'nik')->ignore($pasien),
            ],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', Rule::enum(JenisKelamin::class)],
            'alamat' => ['required', 'string', 'max:1000'],
            'no_telp' => ['required', 'string', 'max:20'],
            'golongan_darah' => ['nullable', Rule::enum(GolonganDarah::class)],

            // Akun pengguna terkait (jika ada)
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($pasien?->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.size' => 'NIK harus 16 digit.',
            'nik.regex' => 'NIK hanya boleh berisi angka.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'golongan_darah.enum' => 'Golongan darah tidak valid.',
            'jenis_kelamin.enum' => 'Jenis kelamin tidak valid.',
        ];
    }
}
