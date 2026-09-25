<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }} — Admin</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-neutral-bg text-neutral-text">
        <div class="min-h-screen lg:flex">

            {{-- Sidebar (project.md Bab 7.5 — layout Admin) --}}
            <aside class="fixed inset-y-0 left-0 z-30 w-64 -translate-x-full bg-primary-dark text-white transition-transform lg:translate-x-0 lg:static lg:shrink-0"
                   x-data="{ open: false }" :class="open ? 'translate-x-0' : '-translate-x-full'">
                <div class="flex h-16 items-center gap-2 border-b border-white/10 px-6">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/15 text-sm font-bold">RS</span>
                    <div class="leading-tight">
                        <p class="text-sm font-semibold">{{ config('app.name') }}</p>
                        <p class="text-xs text-white/60">Administrasi</p>
                    </div>
                </div>

                <nav class="space-y-1 p-4 text-sm">
                    @foreach ([
                        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10'],
                        ['label' => 'Poli',      'route' => 'admin.poli.index', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        ['label' => 'Dokter',    'route' => 'admin.dokter.index', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                        ['label' => 'Jadwal',    'route' => 'admin.jadwal.index', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['label' => 'Laporan',   'route' => 'admin.laporan.index', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ] as $item)
                        @if (Route::has($item['route']))
                            <a href="{{ route($item['route']) }}"
                               @class([
                                   'flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium transition',
                                   'bg-white/15 text-white' => request()->routeIs($item['route']),
                                   'text-white/70 hover:bg-white/10 hover:text-white' => ! request()->routeIs($item['route']),
                               ])>
                                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                                </svg>
                                {{ $item['label'] }}
                            </a>
                        @endif
                    @endforeach
                </nav>

                <div class="absolute inset-x-0 bottom-0 border-t border-white/10 p-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-white/70 transition hover:bg-white/10 hover:text-white">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>

            <div class="flex min-h-screen flex-1 flex-col">
                <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 sm:px-6">
                    <button type="button" @click="open = !open" class="lg:hidden text-slate-600 hover:text-slate-900" aria-label="Buka menu">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div class="ml-auto flex items-center gap-3">
                        <span class="hidden text-sm text-slate-500 sm:inline">{{ now()->translatedFormat('l, d F Y') }}</span>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-200">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            {{ auth()->user()->name }}
                        </a>
                    </div>
                </header>

                <main class="flex-1 p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
