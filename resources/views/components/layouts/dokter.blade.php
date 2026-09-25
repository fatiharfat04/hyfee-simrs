<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }} — Dokter</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans antialiased bg-neutral-bg text-neutral-text">
        {{-- Layout minim distraksi (project.md Bab 7.5) --}}
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white">
            <div class="mx-auto flex h-16 max-w-6xl items-center gap-4 px-4 sm:px-6">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-sm font-bold text-white">RS</span>
                <div class="leading-tight">
                    <p class="text-sm font-semibold">{{ config('app.name') }}</p>
                    <p class="text-xs text-slate-500">Ruang Dokter</p>
                </div>

                <nav class="ml-auto flex items-center gap-2 text-sm">
                    @if (Route::has('dokter.dashboard'))
                        <a href="{{ route('dokter.dashboard') }}"
                           @class(['rounded-lg px-3 py-2 font-medium', 'bg-slate-100 text-slate-900' => request()->routeIs('dokter.*'), 'text-slate-600 hover:bg-slate-50' => ! request()->routeIs('dokter.*')])>
                            Antrean
                        </a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="hidden rounded-lg px-3 py-2 font-medium text-slate-600 hover:bg-slate-50 sm:block">
                        Profil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 font-medium text-slate-600 hover:bg-slate-50">Keluar</button>
                    </form>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-8">
            {{ $slot }}
        </main>

        @stack('scripts')
    </body>
</html>
