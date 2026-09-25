<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-neutral-bg font-sans text-base antialiased text-neutral-text pb-16"
          data-notifikasi-user-id="{{ auth()->user()->id }}">
        {{-- Layout mobile-first, card-based (project.md Bab 7.5) --}}
        <header class="sticky top-0 z-20 bg-primary text-white shadow">
            <div class="mx-auto flex h-14 max-w-lg items-center gap-3 px-4">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 text-xs font-bold">RS</span>
                <div class="leading-tight">
                    <p class="text-sm font-semibold">{{ config('app.name') }}</p>
                    <p class="text-[11px] text-white/70">{{ $title ?? 'Antrean Rawat Jalan' }}</p>
                </div>

                {{-- Lonceng notifikasi (project.md Bab 5.2) --}}
                @php
                    $jumlahBelumDibaca = auth()->user()->unreadNotifications()->count();
                    $daftarNotifikasi = auth()->user()->notifications()->latest()->take(8)->get();
                @endphp

                <div x-data="{ lonceng: false }" class="relative ml-auto">
                    <button type="button"
                            class="relative flex h-8 w-8 items-center justify-center rounded-full bg-white/15 hover:bg-white/25"
                            aria-label="Notifikasi"
                            :aria-expanded="lonceng"
                            @click="lonceng = !lonceng">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.857 23.857 0 005.454-1.31A8.97 8.97 0 0118 9.75V9A6 6 0 006 9v.75a8.97 8.97 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        <span id="notifikasi-badge"
                              @class([
                                  'absolute -right-1 -top-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-danger px-1 text-[10px] font-bold leading-none',
                                  'hidden' => $jumlahBelumDibaca === 0,
                              ])>{{ $jumlahBelumDibaca }}</span>
                    </button>

                    <div x-cloak
                         x-show="lonceng"
                         x-transition.origin.top.right
                         @click.outside="lonceng = false"
                         class="absolute right-0 top-10 z-30 w-72 overflow-hidden rounded-xl bg-white text-neutral-text shadow-xl ring-1 ring-black/10">
                        <div class="flex items-center justify-between border-b border-slate-100 px-3 py-2">
                            <p class="text-xs font-semibold">Notifikasi</p>
                            @if ($jumlahBelumDibaca > 0)
                                <form method="POST" action="{{ route('pasien.notifikasi.bacaSemua') }}">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-medium text-primary hover:underline">Tandai semua</button>
                                </form>
                            @endif
                        </div>

                        <ul class="max-h-72 divide-y divide-slate-100 overflow-y-auto">
                            @forelse ($daftarNotifikasi as $notifikasi)
                                <li>
                                    <form method="POST" action="{{ route('pasien.notifikasi.baca', $notifikasi) }}">
                                        @csrf
                                        <button type="submit" class="flex w-full gap-2 px-3 py-2 text-left hover:bg-slate-50">
                                            <span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full {{ $notifikasi->read_at ? 'bg-transparent' : 'bg-danger' }}"></span>
                                            <span class="min-w-0">
                                                <span class="block truncate text-xs font-semibold">{{ $notifikasi->data['judul'] ?? 'Notifikasi' }}</span>
                                                <span class="block text-[11px] leading-snug text-slate-500">{{ $notifikasi->data['pesan'] ?? '' }}</span>
                                                <span class="block text-[10px] text-slate-400">{{ $notifikasi->created_at->diffForHumans() }}</span>
                                            </span>
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="px-3 py-4 text-center text-[11px] text-slate-400">Belum ada notifikasi.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <a href="{{ route('profile.edit') }}" class="flex h-8 w-8 items-center justify-center rounded-full bg-white/15 text-sm font-semibold hover:bg-white/25" aria-label="Profil">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </a>
            </div>
        </header>

        <main class="mx-auto max-w-lg px-4 py-4">
            {{ $slot }}
        </main>

        {{-- Bottom navigation --}}
        <nav class="fixed inset-x-0 bottom-0 z-20 border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)]">
            <div class="mx-auto grid max-w-lg grid-cols-3">
                @foreach ([
                    ['label' => 'Beranda', 'route' => 'pasien.dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10'],
                    ['label' => 'Daftar Antrean', 'route' => 'pasien.antrean.create', 'icon' => 'M12 4v16m8-8H4'],
                    ['label' => 'Antrean Saya', 'route' => 'pasien.antrean.mine', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                ] as $item)
                    @if (Route::has($item['route']))
                        <a href="{{ route($item['route']) }}"
                           @class([
                               'flex flex-col items-center gap-0.5 py-2 text-[11px] font-medium',
                               'text-primary' => request()->routeIs($item['route']),
                               'text-slate-500' => ! request()->routeIs($item['route']),
                           ])>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </nav>

        @stack('scripts')
    </body>
</html>
