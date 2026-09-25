import './bootstrap';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

/**
 * Papan antrean publik (project.md Prompt 6).
 *
 * Update nomor antrean datang dari broadcast event AntreanDipanggil
 * (Laravel Echo + Reverb) — bukan polling AJAX, supaya ringan saat
 * dipasang di banyak monitor TV.
 */
const el = (id) => document.getElementById(id);

const body = document.body;
const poliId = Number(body.dataset.poliId);

const kodeSekarang = el('kode-sekarang');
const dokterSekarang = el('dokter-sekarang');
const ruangSekarang = el('ruang-sekarang');
const jamSekarang = el('jam-sekarang');
const kartuSekarang = el('kartu-sekarang');
const daftarBerikut = el('daftar-berikutnya');
const daftarTerakhir = el('daftar-terakhir');
const suaraAktif = el('suara-aktif');
const suaraNonaktif = el('suara-nonaktif');

/** Sisa antrean menunggu untuk poli ini (dikirim server sekali di awal). */
let antreanMenunggu = Array.isArray(window.ANTREAN_MENUNGGU)
    ? [...window.ANTREAN_MENUNGGU]
    : [];

/** Daftar nomor yang sudah dipanggil (ditampilkan di footer). */
let sudahDipanggil = Array.isArray(window.ANTREAN_DIPANGGIL)
    ? [...window.ANTREAN_DIPANGGIL]
    : [];

const JUMLAH_BERIKUTNYA = 5;
const JUMLAH_TERAKHIR = 6;

/* -------------------------------------------------------------------------- */
/*  Jam berjalan                                                               */
/* -------------------------------------------------------------------------- */
function perbaruiJam() {
    if (!jamSekarang) return;
    jamSekarang.textContent = new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    })
        .format(new Date())
        .replace(/\./g, ':');
}
perbaruiJam();
setInterval(perbaruiJam, 1000);

/* -------------------------------------------------------------------------- */
/*  Suara notifikasi (WebAudio — tanpa file audio)                             */
/* -------------------------------------------------------------------------- */
let audioCtx = null;
let suaraDipakai = false;

function bukaAudio() {
    if (!audioCtx) {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        audioCtx = new AudioContext();
    }
    if (audioCtx.state === 'suspended') audioCtx.resume();
    suaraDipakai = true;
    if (suaraAktif) suaraAktif.classList.remove('hidden');
    if (suaraNonaktif) suaraNonaktif.classList.add('hidden');
}

// Browser melarang autoplay sampai user berinteraksi sekali.
['click', 'keydown', 'touchstart'].forEach((nama) =>
    document.addEventListener(nama, bukaAudio, { once: true, passive: true })
);

function bunyikanNotifikasi() {
    if (!suaraDipakai || !audioCtx) return;

    const mulai = audioCtx.currentTime;

    [880, 660, 880].forEach((frekuensi, index) => {
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();

        osc.type = 'sine';
        osc.frequency.value = frekuensi;

        const t = mulai + index * 0.28;
        gain.gain.setValueAtTime(0.0001, t);
        gain.gain.exponentialRampToValueAtTime(0.35, t + 0.03);
        gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.24);

        osc.connect(gain).connect(audioCtx.destination);
        osc.start(t);
        osc.stop(t + 0.26);
    });
}

/* -------------------------------------------------------------------------- */
/*  Render daftar                                                              */
/* -------------------------------------------------------------------------- */
function renderBerikutnya() {
    if (!daftarBerikut) return;

    const daftar = antreanMenunggu.slice(0, JUMLAH_BERIKUTNYA);

    if (daftar.length === 0) {
        daftarBerikut.innerHTML =
            '<li class="py-4 text-center text-2xl font-semibold text-white/40">— Antrean kosong —</li>';
        return;
    }

    daftarBerikut.innerHTML = daftar
        .map(
            (kode, index) => `
        <li class="flex items-center justify-between gap-6 border-b border-white/10 px-6 py-4 last:border-b-0">
            <span class="text-xl uppercase tracking-widest text-white/50">Nomor ${index + 1}</span>
            <span class="font-mono text-5xl font-bold text-white">${kode}</span>
        </li>`
        )
        .join('');
}

function renderTerakhir() {
    if (!daftarTerakhir) return;

    const daftar = sudahDipanggil.slice(0, JUMLAH_TERAKHIR);

    daftarTerakhir.innerHTML =
        daftar.length === 0
            ? '<li class="text-white/40">Belum ada nomor dipanggil</li>'
            : daftar
                  .map(
                      (kode) =>
                          `<li class="font-mono text-3xl font-bold text-white/70">${kode}</li>`
                  )
                  .join('');
}

function renderSekarang({ kode, dokter, ruang }) {
    if (kodeSekarang) kodeSekarang.textContent = kode;
    if (dokterSekarang) dokterSekarang.textContent = dokter || '';
    if (ruangSekarang) ruangSekarang.textContent = ruang || '';
}

renderBerikutnya();
renderTerakhir();

/* -------------------------------------------------------------------------- */
/*  Highlight animasi saat nomor baru dipanggil                                */
/* -------------------------------------------------------------------------- */
function sorot() {
    if (!kartuSekarang) return;

    kartuSekarang.classList.remove('ring-4', 'ring-amber-400');
    // paksa reflow supaya animasi bisa diulang
    void kartuSekarang.offsetWidth;
    kartuSekarang.classList.add('ring-4', 'ring-amber-400');

    if (kodeSekarang) {
        kodeSekarang.classList.remove('animate-terpanggil');
        void kodeSekarang.offsetWidth;
        kodeSekarang.classList.add('animate-terpanggil');
    }

    setTimeout(() => kartuSekarang?.classList.remove('ring-4', 'ring-amber-400'), 6000);
}

/* -------------------------------------------------------------------------- */
/*  Event broadcast                                                            */
/* -------------------------------------------------------------------------- */
function tanganiPanggilan(payload) {
    if (!payload || Number(payload.poli?.id) !== poliId) return; // poli lain: abaikan

    const kode = payload.kode_antrean;

    // Nomor yang sedang tampil pindah ke riwayat.
    const sebelumnya = kodeSekarang?.textContent?.trim();
    if (sebelumnya && sebelumnya !== '—' && sebelumnya !== kode) {
        sudahDipanggil = [sebelumnya, ...sudahDipanggil];
    }

    antreanMenunggu = antreanMenunggu.filter((k) => k !== kode);
    sudahDipanggil = sudahDipanggil.filter((k) => k !== kode);

    renderSekarang({
        kode,
        dokter: payload.dokter,
        ruang: payload.poli?.lokasi_ruang,
    });
    renderBerikutnya();
    renderTerakhir();
    sorot();
    bunyikanNotifikasi();
}

const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

echo.channel('papan-antrean').listen('.AntreanDipanggil', tanganiPanggilan);

// Indikator koneksi di sudut layar (hanya untuk operator).
const indikator = el('indikator-koneksi');
if (indikator) {
    window.Echo.connector?.pusher?.connection?.bind('state_change', ({ current }) => {
        indikator.textContent = current === 'connected' ? 'Live' : `Koneksi: ${current}`;
        indikator.classList.toggle('bg-amber-400', current !== 'connected');
        indikator.classList.toggle('bg-emerald-400', current === 'connected');
    });
}
