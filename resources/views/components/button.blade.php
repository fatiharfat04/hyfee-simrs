@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])

@php
    $variants = [
        'primary' => 'bg-primary text-white hover:bg-primary-dark focus-visible:outline-primary',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 focus-visible:outline-slate-400',
        'success' => 'bg-success text-white hover:bg-green-700 focus-visible:outline-success',
        'warning' => 'bg-warning text-white hover:bg-amber-700 focus-visible:outline-warning',
        'danger' => 'bg-danger text-white hover:bg-red-700 focus-visible:outline-danger',
        'ghost' => 'text-slate-600 hover:bg-slate-100 focus-visible:outline-slate-400',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-6 py-4 text-base sm:text-lg',
    ];
@endphp

<button
    {{ $attributes->merge(['type' => $type])->class([
        'inline-flex items-center justify-center gap-2 rounded-lg font-semibold shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
        $variants[$variant] ?? $variants['primary'],
        $sizes[$size] ?? $sizes['md'],
    ]) }}
>
    {{ $slot }}
</button>
