<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePoliRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi level route: role:admin
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kode_poli' => ['required', 'string', 'max:10', Rule::unique('polis', 'kode_poli')],
            'nama_poli' => ['required', 'string', 'max:255'],
            'prefix_antrean' => ['required', 'string', 'size:1', 'alpha'],
            'lokasi_ruang' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_poli.unique' => 'Kode poli sudah dipakai.',
            'prefix_antrean.size' => 'Prefix antrean harus 1 huruf.',
            'prefix_antrean.alpha' => 'Prefix antrean harus berupa huruf.',
        ];
    }
}
