# Sistem Informasi Pengelolaan Kas BEM

merupakan sistem yang dirancang untuk membantu pengelolaan keuangan organisasi BEM secara terstruktur dan transparan. Sistem ini mencakup pencatatan pemasukan dan pengeluaran, pengelolaan saldo kas, pemantauan transaksi, serta pembuatan laporan keuangan sehingga proses administrasi keuangan menjadi lebih efektif, akurat, dan mudah dipantau.

## 1. Teknologi

- Backend: Laravel 12, PHP 8.2+, Eloquent ORM, Laravel session authentication.
- Frontend: Laravel Blade templates, HTML, CSS, JavaScript, Chart.js.
- Database: PostgreSQL pada Supabase.

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

## 3. Instalasi dan menjalankan

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

## 4. Akun demo

Password semua akun: `password`.

| Role | Email |
|---|---|
| Bendahara | `bendahara@bemfilkom.test` |
| Ketua BEM | `ketua@bemfilkom.test` |
| Anggota | `anggota1@bemfilkom.test` |
| Anggota tambahan | `anggota2@bemfilkom.test` s.d. `anggota10@bemfilkom.test` |

Bendahara juga dibuat sebagai anggota aktif dan menerima tagihan pribadi. Dengan demikian, akun Bendahara dapat mendemokan fungsi administratif sekaligus alur anggota (membayar iuran sendiri).

## 5. Fitur dan hak akses

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

## 6. Status dan aturan bisnis

- Status tagihan: `belum_bayar`, `menunggu_verifikasi`, `lunas`, `ditolak`.
- Status transaksi pembayaran: `menunggu_verifikasi`, `diterima`, `ditolak`.
- Transfer tidak dilakukan oleh aplikasi. Sistem hanya mencatat tanggal/metode dan bukti transfer manual.
- Pembayaran belum menambah saldo sebelum diverifikasi.
- `cash_transactions.payment_id` unik mencegah satu pembayaran menghasilkan pemasukan ganda.
- Pengajuan ulang setelah ditolak menghasilkan catatan pembayaran baru; histori pengajuan sebelumnya tetap ada.
- Reminder dihitung dari tagihan belum lunas yang jatuh tempo dalam 7 hari atau telah lewat tenggat.
