/**
 * Toast notifikasi real-time di halaman pasien (project.md Bab 5.2 / Prompt 7).
 *
 * Channel private `App.Models.User.{id}` menerima payload channel `broadcast`
 * dari NotificationService, lalu muncul sebagai toast + badge lonceng naik.
 * Channel database tetap dipakai sebagai sumber daftar pada lonceng.
 */

const bodi = document.body;
const userId = bodi?.dataset?.notifikasiUserId;

/** Warna border kiri sesuai tipe notifikasi. */
function warnaTipe(tipe) {
    switch (tipe) {
        case 'giliran':
            return '#DC2626'; // danger — giliran dipanggil, wajib ke ruang
        case 'peringatan':
            return '#D97706'; // warning — 2 nomor sebelum giliran
        default:
            return '#0F766E'; // primary
    }
}

function wadahToast() {
    let wadah = document.getElementById('wadah-toast-notifikasi');

    if (! wadah) {
        wadah = document.createElement('div');
        wadah.id = 'wadah-toast-notifikasi';
        wadah.setAttribute('aria-live', 'polite');
        wadah.setAttribute('aria-atomic', 'true');
        wadah.className =
            'pointer-events-none fixed inset-x-0 bottom-16 z-50 flex flex-col items-center gap-2 px-4';
        document.body.appendChild(wadah);
    }

    return wadah;
}

/**
 * @param {{judul?: string, pesan?: string, tipe?: string}} isi
 */
export function tampilkanToast(isi) {
    const judul = isi?.judul || 'Notifikasi Antrean';
    const pesan = isi?.pesan || '';

    const toast = document.createElement('div');
    toast.setAttribute('role', 'status');
    toast.className =
        'pointer-events-auto w-full max-w-sm translate-y-4 rounded-xl bg-white p-3 shadow-lg ring-1 ring-black/5 opacity-0 transition duration-300 ease-out';
    toast.style.borderLeft = `4px solid ${warnaTipe(isi?.tipe)}`;

    const baris = document.createElement('div');
    baris.className = 'flex items-start gap-2';

    const teks = document.createElement('div');
    teks.className = 'min-w-0 flex-1';

    const elJudul = document.createElement('p');
    elJudul.className = 'text-xs font-semibold text-neutral-text';
    elJudul.textContent = judul;
    teks.appendChild(elJudul);

    if (pesan) {
        const elPesan = document.createElement('p');
        elPesan.className = 'mt-0.5 text-[11px] leading-snug text-slate-500';
        elPesan.textContent = pesan;
        teks.appendChild(elPesan);
    }

    const tombolTutup = document.createElement('button');
    tombolTutup.type = 'button';
    tombolTutup.setAttribute('aria-label', 'Tutup notifikasi');
    tombolTutup.className = 'shrink-0 rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600';
    tombolTutup.textContent = '✕';

    baris.appendChild(teks);
    baris.appendChild(tombolTutup);
    toast.appendChild(baris);
    wadahToast().appendChild(toast);

    const tutup = () => {
        toast.classList.add('opacity-0', 'translate-y-4');
        window.setTimeout(() => toast.remove(), 300);
    };

    tombolTutup.addEventListener('click', tutup);
    window.setTimeout(tutup, 8000);

    requestAnimationFrame(() => {
        toast.classList.remove('opacity-0', 'translate-y-4');
    });
}

/** Naikkan angka badge lonceng (dirender server saat load pertama). */
function tambahBadge() {
    const badge = document.getElementById('notifikasi-badge');
    if (! badge) return;

    const sekarang = parseInt(badge.textContent, 10) || 0;
    badge.textContent = String(sekarang + 1);
    badge.classList.remove('hidden');
}

if (userId && window.Echo) {
    try {
        window.Echo.private(`App.Models.User.${userId}`).notification((notif) => {
            // Payload datar dari Notification::toArray(); fallback bila dibungkus.
            const data =
                notif && typeof notif.judul === 'string'
                    ? notif
                    : (notif?.data ?? notif ?? {});

            tampilkanToast({
                judul: data.judul,
                pesan: data.pesan,
                tipe: data.tipe,
            });
            tambahBadge();
        });
    } catch (e) {
        console.warn('Gagal berlangganan channel notifikasi.', e);
    }
}
