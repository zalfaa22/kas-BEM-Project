# SGE Workspace Portal — Sistem Informasi Pengelolaan Kas BEM FILKOM UB

Starter project demonstrasi berbasis **Laravel 12 + Blade + Supabase PostgreSQL**. Folder backend dan frontend dipisahkan di level root agar tanggung jawab kode mudah dijelaskan saat presentasi.

## 1. Teknologi

- Backend: Laravel 12, PHP 8.2+, Eloquent ORM, Laravel session authentication.
- Frontend: Laravel Blade templates, HTML, CSS, JavaScript, Chart.js.
- Database: PostgreSQL pada Supabase.
- Penyimpanan bukti: local Laravel public storage untuk demo lokal. File tidak disimpan di tabel database; tabel `payment_proofs` menyimpan metadata dan path. Untuk deployment, pindahkan file ke Supabase Storage/private bucket.
- Gunakan `php artisan serve` untuk server development Laravel.

> **Penyesuaian terhadap dokumen kelompok:** laporan Modul 2 versi yang diberikan masih menyebut MySQL pada bagian lingkungan operasi. Implementasi ini memakai PostgreSQL melalui Supabase sesuai keputusan teknologi terbaru. Perbarui bagian DBMS, lingkungan operasi, dan rancangan implementasi pada laporan agar konsisten. Entitas dan alur bisnis inti tetap mengikuti dokumen.

## 2. Struktur folder

```text
kas-bem-supabase/
├── backend/                       # Laravel 12 (seluruh logika server)
│   ├── app/
│   │   ├── Http/Controllers/      # AuthController, PortalController
│   │   ├── Http/Middleware/       # RoleMiddleware
│   │   ├── Models/                # User, Department, DuesRule, Invoice,
│   │   │                          # Payment, PaymentProof, CashTransaction, Notice
│   │   └── Providers/
│   ├── bootstrap/                 # Bootstrap Laravel dan path frontend/public
│   ├── config/                    # Konfigurasi aplikasi dan PostgreSQL
│   ├── database/
│   │   ├── migrations/            # Skema PostgreSQL melalui Laravel migrations
│   │   └── seeders/               # Akun dan data demo
│   ├── routes/web.php             # Routing dan proteksi role
│   ├── storage/                   # Log, session, dan file bukti lokal
│   ├── tests/                     # Smoke test
│   ├── .env.example
│   ├── artisan
│   └── composer.json
├── frontend/                      # Seluruh antarmuka yang dirender Laravel
│   ├── views/                     # Blade: auth, dashboard, members, invoices,
│   │                              # dues, payments, transactions, reports, profile
│   └── public/
│       ├── css/app.css            # Tema visual teal/cream/sage
│       ├── js/app.js
│       └── images/                # Banner referensi SGE Workspace
├── docs/
│   ├── arsitektur.md
│   └── pemetaan-modul.md
└── README.md
```

Laravel tetap menjadi satu aplikasi web (monolith) agar autentikasi session, CSRF, Blade, dan validasi file lebih sederhana dan stabil untuk demo. Pemisahan `backend/` dan `frontend/` adalah pemisahan tanggung jawab folder, bukan dua server yang harus dijalankan terpisah.

## 3. Persiapan perangkat

Install:
1. PHP 8.2 atau lebih baru, aktifkan ekstensi `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `json`.
2. Composer 2.
3. VS Code.
4. Akun Supabase.

Cek dari terminal VS Code:

```powershell
php -v
php -m | findstr /I "pdo_pgsql pgsql mbstring fileinfo"
composer -V
```

Jika `pdo_pgsql` belum muncul, aktifkan ekstensi `pdo_pgsql` dan `pgsql` di `php.ini` yang digunakan terminal, lalu tutup dan buka kembali terminal. Laravel tidak dapat terhubung ke Supabase PostgreSQL tanpa ekstensi tersebut.

## 4. Buat project Supabase

1. Buka https://supabase.com/ lalu buat project baru.
2. Simpan database password yang dibuat saat setup.
3. Di dashboard Supabase buka **Connect** atau **Project Settings → Database**.
4. Ambil connection details PostgreSQL. Untuk jaringan IPv4/laptop kampus, gunakan **Session Pooler** bila direct connection tidak dapat dijangkau.
5. Catat host, port, database, username, dan password dari connection string yang disediakan Supabase. Jangan menebak host/username; salin sesuai project sendiri.
6. Tidak perlu membuat tabel manual. Laravel migrations akan membuat tabel di schema `public`.

## 5. Konfigurasi `.env`

Dari folder `backend`, salin `.env.example` menjadi `.env`:

```powershell
Copy-Item .env.example .env
```

Isi konfigurasi Supabase sesuai connection details milikmu:

```env
DB_CONNECTION=pgsql
DB_HOST=HOST_DARI_SUPABASE
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=USERNAME_DARI_SUPABASE
DB_PASSWORD=PASSWORD_DATABASE_SUPABASE
DB_SCHEMA=public
DB_SSLMODE=require
```

Jika memakai Supabase Session Pooler, gunakan port dan username persis seperti yang ditampilkan Supabase. Jangan unggah file `.env` atau membagikan password database.

## 6. Instalasi dan menjalankan

Buka terminal VS Code di folder `kas-bem-supabase/backend`:

```powershell
composer install --prefer-dist
php artisan key:generate
php artisan config:clear
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Buka http://127.0.0.1:8000.

Perintah `migrate --seed` membuat tabel dan akun demo. Jangan memakai `migrate:fresh` pada database yang berisi data penting karena perintah itu menghapus semua tabel.

## 7. Akun demo

Password semua akun: `password`.

| Role | Email |
|---|---|
| Bendahara | `bendahara@bemfilkom.test` |
| Ketua BEM | `ketua@bemfilkom.test` |
| Anggota | `anggota1@bemfilkom.test` |
| Anggota tambahan | `anggota2@bemfilkom.test` s.d. `anggota10@bemfilkom.test` |

Bendahara juga dibuat sebagai anggota aktif dan menerima tagihan pribadi. Dengan demikian, akun Bendahara dapat mendemokan fungsi administratif sekaligus alur anggota (membayar iuran sendiri).

## 8. Fitur dan hak akses

### Bendahara
- Dashboard ringkasan saldo, pemasukan, pengeluaran, anggota, dan verifikasi.
- CRUD data anggota dasar: tambah, ubah data, aktif/nonaktif.
- Membuat aturan iuran, mengaktifkan/menonaktifkan aturan.
- Generate tagihan massal untuk seluruh akun berstatus anggota aktif; kombinasi anggota-periode tidak digandakan.
- Melihat daftar tagihan dan status.
- Memeriksa bukti pembayaran JPG/PNG/PDF maksimal 2 MB, menerima/menolak dengan alasan.
- Penerimaan pembayaran mengubah tagihan menjadi `lunas` dan mencatat pemasukan satu kali.
- Menolak pembayaran mengubah tagihan menjadi `ditolak`; anggota dapat mengunggah ulang.
- Mencatat pengeluaran, melihat transaksi, laporan, dan ekspor CSV.
- Mengakses Tagihan Pribadi dan mengirim bukti pembayaran untuk iurannya sendiri.

### Anggota
- Melihat tagihan milik sendiri saja.
- Melakukan transfer manual di luar sistem dan mengunggah bukti.
- Melihat status `belum_bayar`, `menunggu_verifikasi`, `lunas`, atau `ditolak`.
- Melihat tagihan/riwayat pribadi, reminder, notifikasi, dan profil.
- Tidak dapat membuat tagihan, mengubah nominal, atau memverifikasi pembayaran.

### Ketua BEM
- Dashboard monitoring read-only: saldo, pemasukan, pengeluaran, status pembayaran, tunggakan, dan transaksi terbaru.
- Tidak mendapat akses ke menu operasional Bendahara.

## 9. Alur demo yang disarankan

1. Login Bendahara → dashboard.
2. Buka Data Anggota → tambah anggota atau ubah status anggota menjadi nonaktif.
3. Buka Aturan Iuran → buat nominal/periode/tenggat.
4. Buka Tagihan → generate tagihan untuk anggota aktif.
5. Logout, login Anggota → buka Tagihan Saya → unggah bukti JPG/PNG/PDF maksimal 2 MB.
6. Login Bendahara → Verifikasi Pembayaran → buka bukti → terima atau tolak dengan alasan.
7. Jika diterima, status tagihan menjadi lunas dan transaksi pemasukan tercatat otomatis. Jika ditolak, anggota dapat mengunggah bukti ulang.
8. Bendahara mencatat pengeluaran → cek saldo dan laporan.
9. Login Ketua BEM → lihat monitoring read-only.
10. Login Bendahara → Tagihan Pribadi untuk menunjukkan Bendahara juga dapat membayar iuran sebagai anggota.

## 10. Status dan aturan bisnis

- Status tagihan: `belum_bayar`, `menunggu_verifikasi`, `lunas`, `ditolak`.
- Status transaksi pembayaran: `menunggu_verifikasi`, `diterima`, `ditolak`.
- Transfer tidak dilakukan oleh aplikasi. Sistem hanya mencatat tanggal/metode dan bukti transfer manual.
- Pembayaran belum menambah saldo sebelum diverifikasi.
- `cash_transactions.payment_id` unik mencegah satu pembayaran menghasilkan pemasukan ganda.
- Pengajuan ulang setelah ditolak menghasilkan catatan pembayaran baru; histori pengajuan sebelumnya tetap ada.
- Reminder dihitung dari tagihan belum lunas yang jatuh tempo dalam 7 hari atau telah lewat tenggat.

## 11. Uji cepat

```powershell
php artisan route:list
php artisan migrate:status
php artisan test
```

Uji proses bisnis manual menggunakan walkthrough pada bagian 9. Koneksi Supabase harus tersedia saat migration dan aplikasi dijalankan.

## 12. Batasan starter

- File bukti disimpan di `backend/storage/app/public` untuk demo lokal. Database metadata ada di Supabase. Untuk aplikasi yang di-host, pindahkan bukti ke Supabase Storage atau object storage private dan gunakan signed URL/policy.
- Reminder saat ini tampil di aplikasi, belum mengirim email/WhatsApp/push notification terjadwal.
- Ekspor laporan tersedia dalam CSV (dapat dibuka di Excel) dan Print/Save PDF melalui browser; belum ada generator file `.xlsx` khusus.
- Data demo bukan data keuangan asli.
- Sebelum produksi, tambahkan HTTPS, audit log, backup, policy storage private, pengujian otomatis menyeluruh, dan pengaturan akses yang ditinjau.
