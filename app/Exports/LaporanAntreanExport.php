<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Ekspor laporan rekap antrean ke Excel (project.md Prompt 8).
 */
class LaporanAntreanExport implements FromView
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(private readonly array $data) {}

    public function view(): View
    {
        return view('admin.laporan.excel', $this->data);
    }
}
