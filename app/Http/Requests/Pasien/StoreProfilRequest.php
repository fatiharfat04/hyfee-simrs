<?php

namespace App\Http\Requests\Pasien;

use App\Enums\GolonganDarah;
use App\Enums\JenisKelamin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProfilRequest extends FormRequest
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
        return [
            'nik' => [
                'required', 'string', 'regex:/^[0-9]{16}$/',
                Rule::unique('pasiens', 'nik')->ignore($this->user()?->pasien?->id),
            ],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', Rule::enum(JenisKelamin::class)],
            'alamat' => ['required', 'string', 'max:1000'],
            'no_telp' => ['required', 'string', 'max:20'],
            'golongan_darah' => ['nullable', Rule::enum(GolonganDarah::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.regex' => 'NIK harus terdiri dari 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
        ];
    }
}
