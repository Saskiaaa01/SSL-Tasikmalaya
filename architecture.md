# Arsitektur SSLGuard

## 1. Gambaran Umum
Aplikasi **SSLGuard** dibangun sebagai aplikasi full‑stack modern dengan pemisahan jelas antara **frontend**, **backend**, dan **lapisan data**. Tujuan utama adalah memantau masa berlaku sertifikat SSL/TLS semua domain dan subdomain yang dikelola oleh Dinas Komunikasi dan Informatika (Kominfo) serta memberikan notifikasi melalui WhatsApp.

## 2. Teknologi yang Digunakan
| Lapisan | Teknologi | Alasan |
|--------|-----------|--------|
| Frontend | **Laravel Blade** + **Tailwind CSS** + **Vite** | UI server‑side rendering yang terintegrasi dengan Laravel, styling cepat dengan Tailwind, asset bundling via Vite |
| Backend | **Laravel 12.x** (PHP 8.2) | Framework stabil dengan built‑in authentication, scheduler, queue, dan Eloquent ORM |
| Database | **PostgreSQL** | Relasional, transaksi ACID, skalabilitas tinggi |
| Auth | **Laravel Authentication** (guard, middleware) + **role‑based access** | Manajemen peran Administrator & Website Owner |
| Scheduler | **Laravel Scheduler** | Menjalankan pengecekan SSL harian pada pukul 07:00 WIB |
| Queue | **Laravel Queue (database driver)** | Memproses pengecekan SSL dan pengiriman notifikasi secara asynchronous |
| Notifikasi | **WhatsApp Cloud API** (HTTP client) | Pengiriman pesan H‑30, H‑14, H‑7, dan EXPIRED |
| CI/CD | **GitHub Actions** (optional) | Otomatisasi test, lint, dan deploy |

## 3. Struktur Direktori Laravel
```
project-root/
├─ app/                 # Controllers, Models, Services, Jobs, Policies
├─ bootstrap/           # framework boot files
├─ config/              # konfigurasi aplikasi
├─ database/            # migrations, seeders, factories
├─ public/              # index.php, assets
├─ resources/           # Blade views, Tailwind CSS, Vite entry
│   ├─ css/app.css
│   ├─ js/app.js
│   └─ views/...
├─ routes/              # web.php, api.php, console.php
├─ storage/             # logs, cache, queue tables
├─ tests/               # PHPUnit / Pest tests
├─ vendor/              # composer packages
├─ .env.example         # environment placeholders
├─ artisan              # Laravel CLI
└─ composer.json
```

## 4. Alur Data Utama
1. **Pengguna** mengakses aplikasi melalui browser → UI Blade.
2. UI menampilkan **dashboard** yang memanggil route Laravel untuk menampilkan data domain & status sertifikat.
3. **Scheduler** (`php artisan schedule:run`) men-trigger job `CheckDomainSslJob` setiap hari pukul 07:00 WIB.
4. Job melakukan **SSL check** via `SslCheckerService` (SSRF protection, timeout 10 s) dan menyimpan hasil di tabel `ssl_checks`.
5. Berdasarkan sisa hari, job men‑dispatch `SendWhatsappNotificationJob` untuk notifikasi H‑30, H‑14, H‑7, atau EXPIRED (idempotent).
6. Semua aktivitas (login, CRUD domain, pengecekan, notifikasi) dicatat di tabel `audit_logs`.

## 5. Keamanan & Non‑Functional
- **SSRF Protection**: validasi DNS → IP, blokir private / loopback / link‑local.
- **Timeout**: koneksi TLS dibatasi 10 detik; job timeout 60 detik.
- **Rate Limiting**: Laravel throttle middleware pada route API.
- **Audit Log**: menyimpan `user_id`, `action`, `ip_address`, `timestamp`.
- **Secrets Management**: semua kredensial (WhatsApp token, DB password) disimpan di `.env` dan tidak masuk ke repo.
- **Timezone**: aplikasi dan scheduler di‑set ke `Asia/Jakarta`.
- **Database Queue**: menggunakan driver `database` (tidak memerlukan Redis).

## 6. Dokumentasi & Pengujian
- **README.md** berisi langkah instalasi, konfigurasi `.env`, migrasi DB, menjalankan queue worker (`php artisan queue:work --timeout=60`), scheduler (`cron * * * * * php artisan schedule:run`), dan cara menjalankan tes (`php artisan test`).
- **Feature Tests** meliputi autentikasi, otorisasi peran, SSL checker (valid, expired, SSRF blocked), notifikasi idempotent, export CSV, dan audit log.

---
*Dokumen ini telah disesuaikan dengan keputusan teknis terbaru: Laravel + Blade + Tailwind CSS + PostgreSQL.*