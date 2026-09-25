import './bootstrap';

// Echo (via bootstrap) + toast notifikasi untuk halaman pasien.
// Diimpor sebelum Alpine.start() karena berjalan saat module evaluation.
import './notifikasi';

import Alpine from 'alpinejs';

// Livewire (dipakai panel dokter) membundel Alpine sendiri dan menempatkan
// instance-nya ke window.Alpine. Script Livewire adalah classic script di
// <body>, sedangkan module ini defer — jadi urutannya selalu Livewire dulu.
//
// Kita hanya boleh start Alpine milik Breeze di halaman TANPA Livewire,
// supaya tidak ada dua instance Alpine yang sama-sama start() dan
// menggandakan binding directive (dropdown/modal jadi toggle dua kali).
const memuatLivewire =
    !!window.Alpine || !!document.querySelector('script[data-update-uri]');

if (!memuatLivewire) {
    window.Alpine = Alpine;
    Alpine.start();
}
