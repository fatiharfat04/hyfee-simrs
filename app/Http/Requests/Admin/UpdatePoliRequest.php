<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePoliRequest extends FormRequest
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
            'kode_poli' => [
                'required', 'string', 'max:10',
                Rule::unique('polis', 'kode_poli')->ignore($this->route('poli')),
            ],
            'nama_poli' => ['required', 'string', 'max:255'],
            'prefix_antrean' => ['required', 'string', 'size:1', 'alpha'],
            'lokasi_ruang' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
