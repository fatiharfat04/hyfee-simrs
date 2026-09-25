<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Papan Antrean {{ $poli->nama_poli }} — {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/papan.js'])

    <style>
        /* Nomor dipanggil: highlight + hint suara (project.md Prompt 6) */
        @keyframes terpanggil {
            0%   { transform: scale(1);    text-shadow: 0 0 0 rgba(251, 191, 36, 0); }
            40%  { transform: scale(1.10); text-shadow: 0 0 48px rgba(251, 191, 36, .95); }
            100% { transform: scale(1);    text-shadow: 0 0 0 rgba(251, 191, 36, 0); }
        }
        .animate-terpanggil { animation: terpanggil 1.1s ease-in-out 3; }

        @keyframes masuk { from { opacity: .35; transform: translateY(-8px); } to { opacity: 1; transform: none; } }
        .animate-masuk { animation: masuk .45s ease-out; }

        html, body { height: 100%; }
    </style>

    {{-- Data awal antrean: hanya dikirim sekali, lalu di-update oleh broadcast. --}}
    <script>
        window.ANTREAN_MENUNGGU = @json($menunggu);
        window.ANTREAN_DIPANGGIL = @json($sudahDipanggil);
    </script>
</head>

{{-- Body full-screen landscape tanpa navigasi (project.md Bab 7.5) --}}
<body data-poli-id="{{ $poli->id }}"
      class="flex h-screen w-screen flex-col overflow-hidden bg-slate-950 font-sans text-white antialiased">

    {{-- Header --}}
    <header class="flex shrink-0 items-center justify-between gap-8 bg-primary px-8 py-5">
        <div class="flex items-center gap-5">
            <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-white/15 text-2xl font-black">RS</span>
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-white/70">Sistem Antrean</p>
                <p class="text-4xl font-extrabold leading-tight">{{ $poli->nama_poli }}</p>
            </div>
        </div>

        <div class="text-right">
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-white/70">Ruang Praktik</p>
            <p class="text-3xl font-bold">{{ $poli->lokasi_ruang }}</p>
        </div>

        <div class="text-right">
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-white/70">Waktu</p>
            <p id="jam-sekarang" class="font-mono text-3xl font-bold">—</p>
        </div>
    </header>

    {{-- Isi --}}
    <main class="grid min-h-0 flex-1 grid-cols-5 gap-6 p-8">

        {{-- Sedang dipanggil --}}
        <section id="kartu-sekarang"
                 class="col-span-3 flex flex-col justify-center rounded-3xl border border-white/10 bg-slate-900 px-10 text-center ring-0 transition duration-500">
            <p class="text-xl font-bold uppercase tracking-[0.35em] text-amber-400">Sedang Dipanggil</p>

            <p id="kode-sekarang"
               class="mt-4 font-mono font-black leading-none text-white"
               style="font-size: clamp(6rem, 13vw, 11rem)">
                {{ $dipanggil?->kode_antrean ?? '—' }}
            </p>

            <div class="mt-6 space-y-2">
                <p id="dokter-sekarang" class="text-3xl font-bold text-white">
                    {{ $dipanggil?->dokter?->namaLengkap() }}
                </p>
                <p class="text-2xl font-semibold text-amber-300">
                    {{ $poli->nama_poli }} · <span id="ruang-sekarang">{{ $poli->lokasi_ruang }}</span>
                </p>
                @if ($dipanggil?->jam_dipanggil)
                    <p class="text-xl text-white/60">
                        Dipanggil pukul {{ strtoupper($dipanggil->jam_dipanggil->format('H:i')) }} WIB
                    </p>
                @endif
            </div>

            <p class="mt-8 text-xl text-white/50">
                Mohon menuju ruang praktik dan menunggu panggilan berikutnya
            </p>
        </section>

        {{-- Nomor berikutnya --}}
        <aside class="col-span-2 flex min-h-0 flex-col rounded-3xl border border-white/10 bg-slate-900">
            <p class="border-b border-white/10 px-6 py-5 text-lg font-bold uppercase tracking-[0.25em] text-white/60">
                Nomor Berikutnya
            </p>

            <ul id="daftar-berikutnya" class="min-h-0 flex-1 divide-y divide-white/5 overflow-hidden">
                @forelse (array_slice($menunggu, 0, 5) as $index => $kode)
                    <li class="flex items-center justify-between gap-6 px-6 py-4 last:border-b-0">
                        <span class="text-xl uppercase tracking-widest text-white/50">Nomor {{ $index + 1 }}</span>
                        <span class="font-mono text-5xl font-bold text-white">{{ $kode }}</span>
                    </li>
                @empty
                    <li class="py-4 text-center text-2xl font-semibold text-white/40">— Antrean kosong —</li>
                @endforelse
            </ul>
        </aside>
    </main>

    {{-- Footer --}}
    <footer class="flex shrink-0 items-center gap-8 border-t border-white/10 bg-slate-900 px-8 py-4">
        <div class="flex items-center gap-4">
            <span class="text-sm font-bold uppercase tracking-[0.25em] text-white/40">Terakhir</span>
            <ul id="daftar-terakhir" class="flex flex-wrap items-center gap-x-5 gap-y-1">
                @forelse ($sudahDipanggil as $kode)
                    <li class="font-mono text-3xl font-bold text-white/70">{{ $kode }}</li>
                @empty
                    <li class="text-white/40">Belum ada nomor dipanggil</li>
                @endforelse
            </ul>
        </div>

        <div class="ml-auto flex items-center gap-6 text-sm text-white/50">
            <span>Menunggu: <strong class="text-white">{{ $rekap['menunggu'] }}</strong></span>
            <span>Selesai: <strong class="text-emerald-400">{{ $rekap['selesai'] }}</strong></span>

            <span id="suara-nonaktif" class="rounded-full bg-amber-400/20 px-3 py-1 font-semibold text-amber-300">
                Klik layar untuk mengaktifkan suara
            </span>
            <span id="suara-aktif" class="hidden rounded-full bg-emerald-400/15 px-3 py-1 font-semibold text-emerald-300">
                Suara aktif
            </span>

            <span id="indikator-koneksi"
                  class="rounded-full bg-amber-400 px-3 py-1 font-bold uppercase tracking-wider text-slate-950">Memuat…</span>
        </div>
    </footer>
</body>
</html>
