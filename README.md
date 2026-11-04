# SSLGuard - Sistem Monitoring Masa Berlaku SSL/TLS

SSLGuard adalah aplikasi pemantauan otomatis masa berlaku sertifikat SSL/TLS untuk seluruh domain dan subdomain yang dikelola oleh Dinas Komunikasi dan Informatika (Diskominfo) Kota Tasikmalaya.

Aplikasi ini mendeteksi status validitas sertifikat secara periodik dan mengirimkan notifikasi peringatan dini melalui WhatsApp Cloud API pada **H-30, H-14, H-7, dan EXPIRED** dengan sistem pencegahan pesan ganda (*idempotency*).

---

## 🚀 Fitur Utama

- **Pemeriksaan TLS Real-Time & Detail:** Memeriksa sertifikat SSL langsung ke server tujuan (masa berlaku, issuer, SHA-256 fingerprint, dan rantai sertifikat/chain).
- **Proteksi SSRF (Server-Side Request Forgery):** Memvalidasi resolusi DNS dan memblokir seluruh jangkauan IP privat/non-publik (IPv4 dan IPv6) sebelum membuka koneksi jaringan.
- **Pencegahan Spam Notifikasi (Idempotency):** Notifikasi WhatsApp H-30, H-14, H-7, dan EXPIRED hanya dikirim 1 kali untuk sidik jari (*fingerprint*) sertifikat yang sama.
- **Role-Based Access Control (RBAC):**
  - **Administrator:** Memiliki hak pantau dan kelola seluruh domain dalam sistem.
  - **Website Owner:** Memiliki hak kelola eksklusif hanya untuk domain yang didaftarkan oleh akunnya.
- **Dashboard Metrik Visual:** Menampilkan ringkasan total domain, sertifikat aktif/valid, segera kadaluarsa, kadaluarsa/error, serta riwayat notifikasi WhatsApp terakhir.
- **Pemeriksaan Terjadwal (Scheduler):** Job otomatis harian dieksekusi setiap pukul **07.00 WIB** (`Asia/Jakarta`).
- **Audit Logging:** Setiap penambahan, pembaruan, penghapusan domain, dan eksekusi SSL check dicatat di tabel `audit_logs`.

---

## 🛠️ Persyaratan Sistem

- PHP >= 8.2 (ekstensi `openssl`, `pdo_sqlite` / `pdo_pgsql`, `curl`, `mbstring`)
- Composer >= 2.0
- Node.js >= 18.x & NPM
- Database: SQLite (default untuk local dev) atau PostgreSQL (production)

---

## ⚙️ Panduan Instalasi Lokal

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/Saskiaaa01/SSL-Tasikmalaya.git
   cd SSL-Tasikmalaya
   ```

2. **Instal Dependensi PHP & JavaScript:**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi WhatsApp Cloud API (Opsional untuk Testing Nyata):**
   Tambahkan kredensial di file `.env`:
   ```env
   WHATSAPP_CLOUD_API_TOKEN=your_token_here
   WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id_here
   WHATSAPP_API_VERSION=v21.0
   ```
   *Catatan: Jika token belum diisi, sistem otomatis masuk ke mode simulasi aman dan mencatat isi notifikasi ke log tanpa error.*

5. **Jalankan Migrasi Database:**
   ```bash
   php artisan migrate
   ```

6. **Menjalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Akses aplikasi di browser pada: `http://127.0.0.1:8000`

---

## 🔄 Menjalankan Background Worker & Scheduler

1. **Queue Worker (untuk memproses pengecekan SSL & kirim WhatsApp):**
   ```bash
   php artisan queue:work --timeout=60
   ```

2. **Scheduler (otomatis harian pukul 07.00 WIB):**
   Untuk pengujian lokal:
   ```bash
   php artisan schedule:run
   ```
   Atau jalankan manual command pemeriksaan seluruh domain:
   ```bash
   php artisan ssl:check-daily
   ```

---

## 🧪 Menjalankan Automated Tests

Aplikasi dilengkapi test suite komprehensif untuk validasi Model, RBAC Policy, Proteksi SSRF, Kalkulasi Masa Berlaku, dan Idempotensi Notifikasi:
```bash
php artisan test
```

---

## 📄 Lisensi

---

## 🛎️ Handling Expired Certificates

When a domain's SSL/TLS certificate expires, the system follows this flow:

1. **Detection**
   - The daily scheduler (`php artisan ssl:check-daily`) runs `CheckDomainSslJob` for each domain.
   - Inside the job the certificate's `days_remaining` is calculated. If the value is **≤ 0**, the status is set to `expired`.

2. **UI Update**
   - The domain detail view (`resources/views/domains/show.blade.php`) displays a red badge:
     ```html
     <span class="badge bg-red-600 text-white">✕ Expired / Kadaluarsa</span>
     ```
   - Hovering the badge shows an error tooltip containing the job‑generated `error_message` (e.g., "certificate has expired").
   - The dashboard (`resources/views/dashboard.blade.php`) similarly shows the expired badge on the domain card.

3. **Notification**
   - An `ExpiredSslNotification` job is dispatched.
   - If WhatsApp Cloud API credentials are present, a WhatsApp message is sent to the configured number. Otherwise the system logs the message content and marks the notification as *simulated*.
   - Idempotency ensures that only **one** notification per fingerprint is sent, avoiding duplicate alerts for the same expired certificate.

4. **Audit Logging**
   - An entry is inserted into the `audit_logs` table with `action = 'ssl_expired'`, the domain ID, timestamp (in the `Asia/Jakarta` timezone), and the fingerprint that triggered the alert.
   - You can view these logs in `storage/logs/laravel.log` or via any admin UI you may add later.

5. **Manual Re‑check (Optional)**
   - To re‑trigger the check for a specific domain without waiting for the daily schedule, send a POST request to:
     ```http
     POST /domains/{domain}/check
     ```
   - The response will include the updated status and, if applicable, a fresh notification entry.

### Where to Find the Information
- **Badge on UI** – visible on the domain detail page and the dashboard.
- **Recent Notifications Table** – located under the *Recent Notifications* section on the dashboard, showing type `expired`, status (`sent`/`simulated`), timestamp, and recipient.
- **Audit Log Table** – in the database (`audit_logs`) and the `storage/logs/laravel.log` file.
- **Timezone** – All timestamps are stored and displayed using the `Asia/Jakarta` timezone as configured in `config/app.php`.

---
Dikembangkan untuk keperluan operasional PKL Dinas Komunikasi dan Informatika Kota Tasikmalaya.
