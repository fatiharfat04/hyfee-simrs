@props(['status' => 'menunggu'])

@php
    // Mapping status badge project.md Bab 7.3
    $map = [
        'menunggu' => 'bg-slate-100 text-slate-700',
        'dipanggil' => 'bg-amber-100 text-amber-800 animate-pulse',
        'dilayani' => 'bg-blue-100 text-blue-800',
        'selesai' => 'bg-green-100 text-green-800',
        'batal' => 'bg-red-100 text-red-700',
        'tidak_hadir' => 'bg-red-50 text-red-600 border border-red-300',
    ];

    $label = [
        'menunggu' => 'Menunggu',
        'dipanggil' => 'Dipanggil',
        'dilayani' => 'Dilayani',
        'selesai' => 'Selesai',
        'batal' => 'Batal',
        'tidak_hadir' => 'Tidak Hadir',
    ];

    $class = $map[$status] ?? $map['menunggu'];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', $class]) }}>
    {{ $label[$status] ?? $status }}
</span>
