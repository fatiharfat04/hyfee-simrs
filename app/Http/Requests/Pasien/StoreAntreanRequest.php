<?php

namespace App\Http\Requests\Pasien;

use Illuminate\Foundation\Http\FormRequest;

class StoreAntreanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cek kepemilikan pasien dilakukan di controller (butuh relasi user->pasien).
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'poli_id' => ['required', 'exists:polis,id'],
            'dokter_id' => ['required', 'exists:dokters,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'poli_id.required' => 'Pilih poli terlebih dahulu.',
            'dokter_id.required' => 'Pilih dokter terlebih dahulu.',
        ];
    }
}
