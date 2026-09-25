# Panduan Deploy — SIMRS Antrean

Checklist dan konfigurasi untuk deploy ke production (`project.md` **Prompt 10**).

File pendukung:

| File | Fungsi |
| --- | --- |
| `.env.production` | Template environment production (di-gitignore) |
| `deploy/supervisor/reverb.conf` | Supervisor program untuk Laravel Reverb |
| `deploy/supervisor/queue-worker.conf` | Supervisor program untuk queue worker |

---

## 1. Prasyarat server

- PHP **8.2+** dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `zip`, `gd`, `bcmath`, **`pcntl`**, **`posix`** (`pcntl`/`posix` wajib untuk Reverb dan `queue:work`).
- **MySQL 8.0+** — migration `antreans` memakai *generated column* bawaan MySQL 8 (project.md Bab 3.7), jadi MySQL 5.7/8.0 lama berisiko ditolak.
- Node.js 20+ & npm (hanya untuk build aset).
- nginx + php-fpm.
- `supervisor` (`apt install supervisor`).

```bash
php -m | grep -E 'pcntl|posix|pdo_mysql'   # pastikan ketiganya ada
```

---

## 2. Ambil kode & dependensi

```bash
git clone <repo> /var/www/hyfee && cd /var/www/hyfee
composer install --no-dev --optimize-autoloader
npm ci
```

Izinkan tulis pada direktori runtime:

```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

---

## 3. Konfigurasi `.env` production

```bash
cp .env.production .env
php artisan key:generate          # mengisi APP_KEY
$EDITOR .env                      # isi password DB, REVERB_APP_SECRET, APP_URL
```

> `.env` dan `.env.production` sama-sama di-gitignore. Jangan menaruh kredensial
> asli di dalam repository — simpan template di password manager / vault server.
>
> **Alternatif (tanpa menyalin):** ekspor `APP_ENV=production` pada lingkungan
> php-fpm, supervisor, dan cron. Laravel akan memuat `.env.production`
> otomatis menggantikan `.env`. Pilih **satu** cara saja supaya seluruh proses
> memakai konfigurasi yang sama.

Checklist isi `.env`:

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` sudah terisi
- [ ] `APP_URL` sudah memakai domain https final
- [ ] `DB_*` menunjuk database production (terpisah dari `simrs_antrean_test`)
- [ ] `SESSION_DRIVER=database`, `QUEUE_CONNECTION=database`, `CACHE_STORE=database`
- [ ] `BROADCAST_CONNECTION=reverb`
- [ ] `REVERB_HOST` = domain yang sama dengan `APP_URL` (lihat §8)
- [ ] `VITE_REVERB_*` sudah benar — **nilai ini di-embed ke bundle saat build**
- [ ] `MAIL_MAILER` disesuaikan bila email verifikasi diaktifkan

---

## 4. Build aset frontend

```bash
# pastikan isi VITE_REVERB_* di .env sudah final sebelum build
npm run build
```

Rebuild setiap kali `REVERB_*` berubah — `resources/js/echo.js` dan
`resources/js/notifikasi.js` membaca `import.meta.env.VITE_REVERB_*` saat build,
sehingga nilai lama akan tetap tertanam kalau build tidak diulang.

---

## 5. Database

```bash
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force     # sekali saja, saat awal
php artisan db:seed --class=MasterDataSeeder --force  # opsional: data contoh
```

Verifikasi jumlah awal:

```bash
php artisan tinker --execute="dump(\App\Models\Poli::count(), \App\Models\Dokter::count());"
```

---

## 6. Optimasi (wajib sebelum dan setiap kali deploy)

```bash
php artisan optimize
```

Perintah `optimize` mengerjakan semuanya sekaligus (project.md Prompt 10):

| Perintah | Hasil |
| --- | --- |
| `php artisan config:cache` | `bootstrap/cache/config.php` |
| `php artisan route:cache` | `bootstrap/cache/routes-v7.php` |
| `php artisan view:cache` | compiled blade di `storage/framework/views` |
| `php artisan event:cache` | `bootstrap/cache/events.php` |

> **Dengan config cache aktif, `.env` tidak lagi dibaca.** Kalau `.env` berubah,
> jalankan `php artisan config:clear` (atau `php artisan optimize:clear`) lalu
> `php artisan optimize` lagi.
>
> Saat deploy perubahan kode: `php artisan optimize:clear` → tarik kode →
> `php artisan optimize`.

---

## 7. Supervisor — Reverb & queue worker

```bash
sudo cp deploy/supervisor/reverb.conf       /etc/supervisor/conf.d/simrs-reverb.conf
sudo cp deploy/supervisor/queue-worker.conf /etc/supervisor/conf.d/simrs-queue.conf

# sesuaikan path /var/www/hyfee dan user bila berbeda
sudo grep -rn "/var/www/hyfee" /etc/supervisor/conf.d/simrs-*.conf

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

Yang dilayani proses tersebut:

| Proses | Tugas |
| --- | --- |
| `simrs-reverb` | Server WebSocket; push `AntreanDipanggil` ke papan antrean & toast pasien |
| `simrs-queue:00`, `:01` | `queue:work --queue=default,broadcast` — mengirim event broadcast & channel `broadcast` notifikasi |

Catatan penting:

- Channel **`database`** notifikasi (project.md Bab 5.2) ditulis langsung ke
  tabel `notifications` tanpa antrean — badge lonceng tetap muncul walau worker
  sedang mati.
- Setelah `.env` berubah: `sudo supervisorctl restart simrs-reverb:* simrs-queue:*`
  atau gunakan `php artisan reverb:restart` dan `php artisan queue:restart`.

---

## 8. nginx — reverse proxy WebSocket Reverb

Reverb mendengarkan `0.0.0.0:6001` (HTTP, tanpa TLS). Port publik 443 dibuka
nginx, yang meneruskan path WebSocket **`/app/`** (koneksi browser) dan
**`/apps/`** (push HTTP dari aplikasi ke Reverb) ke port 6001.

```nginx
server {
    listen 443 ssl http2;
    server_name antrean.example.rs.id;

    root /var/www/hyfee/public;
    index index.php;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    # --- Laravel Reverb (project.md Prompt 6 & 10) ---
    location ~ ^/(app|apps) {
        proxy_pass http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 600s;
        proxy_send_timeout 600s;
        proxy_buffering off;
    }
}
```

```bash
sudo nginx -t && sudo systemctl reload nginx
```

Verifikasi Reverb terhubung:

```bash
sudo supervisorctl status simrs-reverb
tail -f storage/logs/reverb.log
```

---

## 9. Scheduler (opsional)

`QueueService::resetHarian()` bersifat opsional (project.md Bab 5.1 — nomor
antrean sudah di-scope per tanggal, job ini murni arsip/cleanup). Command
`queue:reset-harian` sudah terdaftar dan dijadwalkan harian dari
`routes/console.php` mengikuti `QUEUE_RESET_HOUR` di `.env`.

Aktifkan cron scheduler:

```bash
crontab -e
* * * * * cd /var/www/hyfee && php artisan schedule:run >> /dev/null 2>&1
```

Verifikasi:

```bash
php artisan schedule:list      # harus menampilkan: 0 0 * * *  php artisan queue:reset-harian
php artisan queue:reset-harian # jalankan manual sekali untuk uji
```

Pengaturan terkait di `.env`: `QUEUE_RESET_HOUR=00:00`, `QUEUE_RESET_DAYS=30`
(hapus antrean lebih tua dari ini dengan status selesai/batal/tidak_hadir).

---

## 10. Checklist verifikasi pasca-deploy

Jalankan berurutan setelah deploy; berhenti bila ada yang gagal.

- [ ] `php artisan about` → `Environment: production`, `Debug: false`
- [ ] `php artisan route:list | head` → tanpa error (route cache valid)
- [ ] `php artisan schedule:list` → `queue:reset-harian` terjadwal (bila cron diaktifkan)
- [ ] Halaman login terbuka via https, tanpa `APP_DEBUG` merah
- [ ] `sudo supervisorctl status` → `simrs-reverb` RUNNING, `simrs-queue:00/01` RUNNING
- [ ] Registrasi pasien baru → data identitas tersimpan, masuk ke `pasien.dashboard`
- [ ] Login `admin@simrs.test` → dashboard menampilkan statistik
- [ ] Daftar antrean → nomor antrean terbit (`A-001`), muncul di **Antrean Saya**
- [ ] Buka `https://…/papan-antrean/1` di tab kedua **tanpa login** → menampilkan nomor
- [ ] Dari akun dokter tekan **Panggil Berikutnya** → papan antrean berubah
      **tanpa di-refresh** dan suara berbunyi (bukti Reverb + WebSocket jalan)
- [ ] Lonceng notifikasi pasien bertambah 1 (channel `database`) dan toast muncul
- [ ] `php artisan queue:failed` → kosong
- [ ] Laporan admin → halaman terbuka, export Excel & PDF berhasil
- [ ] `tail -f storage/logs/laravel.log` → tidak ada error baru

Uji otomatis (jalankan di staging/CI, bukan production):

```bash
php artisan test          # seluruh checklist Bab 8 (41 test)
```

---

## 11. Troubleshooting

| Gejala | Penyebab & solusi |
| --- | --- |
| Papan antrean tidak bergerak | Worker mati → `sudo supervisorctl status`; atau `.env` berubah tapi worker tidak di-restart → `php artisan queue:restart` |
| Browser tidak terhubung WebSocket (401/403) | `REVERB_APP_KEY`/`SECRET` tidak sama dengan `VITE_REVERB_APP_KEY` → perbaiki `.env`, lalu **build ulang** `npm run build` |
| Halaman papan menampilkan isyarat koneksi "Memuat…" terus | Blok nginx `/app/` & `/apps/` belum ada, atau `REVERB_PORT`/`REVERB_SCHEME` tidak cocok dengan skema akses |
| Perubahan `.env` tidak berefek | Config sudah di-cache → `php artisan config:clear && php artisan optimize` lalu restart supervisor |
| `Route [dashboard] not defined` | Kode lama; seluruh redirect memakai `$user->dashboardRoute()` |
| Notifikasi tidak masuk | Cek tabel `notifications`; channel `database` tidak butuh worker, sedangkan `broadcast` butuh worker + Reverb |
| Error deadlock `SQLSTATE 1213` | Sudah ditangani `DB::transaction(..., 3)` di `QueueService::daftarAntrean`; bila muncul lagi, pastikan tidak ada custom query `FOR UPDATE` dengan `whereDate()` (non-sargable) |

---

## 12. Rollback

```bash
sudo supervisorctl stop simrs-reverb simrs-queue
php artisan down
# kembalikan kode ke tag sebelumnya
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan optimize:clear && php artisan optimize
# bila migration berubah: php artisan migrate:rollback --force
php artisan up
sudo supervisorctl start simrs-reverb simrs-queue
```

Migration project ini bersifat maju saja (tidak ada `down()` untuk skema hasil
karya sendiri yang sudah final) — pastikan backup database sebelum `migrate`.
