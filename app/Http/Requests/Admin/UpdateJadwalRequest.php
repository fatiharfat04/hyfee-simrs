<?php

namespace App\Http\Requests\Admin;

use App\Enums\Hari;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJadwalRequest extends FormRequest
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
            'dokter_id' => [
                'required',
                'exists:dokters,id',
                Rule::unique('jadwal_dokters', 'dokter_id')
                    ->where('hari', $this->hari)
                    ->where('jam_mulai', $this->jamDalamFormatDb())
                    ->ignore($this->route('jadwal')),
            ],
            'hari' => ['required', Rule::enum(Hari::class)],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'kuota' => ['required', 'integer', 'min:1', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'dokter_id.unique' => 'Jadwal dokter di hari & jam tersebut sudah ada.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'hari.enum' => 'Hari tidak valid.',
        ];
    }

    private function jamDalamFormatDb(): string
    {
        return $this->jam_mulai ? substr($this->jam_mulai, 0, 5).':00' : '';
    }
}
