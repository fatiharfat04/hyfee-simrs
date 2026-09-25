<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Filter laporan admin (project.md Prompt 8): periode harian/bulanan
 * + tanggal acuan, ditambah format ekspor pada route export.
 */
class LaporanRequest extends FormRequest
{
    /**
     * Agar halaman laporan tetap terbuka saat filter belum dipilih.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'periode' => $this->input('periode') ?: 'hari',
            'tanggal' => $this->input('tanggal') ?: today()->toDateString(),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'periode' => ['required', Rule::in(['hari', 'bulan'])],
            'tanggal' => ['required', 'date'],
            'format' => ['sometimes', Rule::in(['excel', 'pdf'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'periode.in' => 'Periode laporan tidak dikenal.',
            'tanggal.date' => 'Tanggal laporan tidak valid.',
            'format.in' => 'Format ekspor hanya excel atau pdf.',
        ];
    }
}
