import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },

            // Palet warna project.md Bab 7.2
            colors: {
                primary: {
                    DEFAULT: '#0F766E', // teal-700 — header, tombol utama, identitas RS
                    dark: '#115E59', // teal-800
                },
                secondary: '#1E40AF', // blue-800 — aksen dokter/info
                success: '#16A34A', // green-600 — status selesai
                warning: '#D97706', // amber-600 — status dipanggil/menunggu lama
                danger: '#DC2626', // red-600 — status batal/tidak hadir
                neutral: {
                    bg: '#F8FAFC', // slate-50 — background halaman
                    text: '#1E293B', // slate-800
                },
            },
        },
    },

    plugins: [forms],
};
