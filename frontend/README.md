# Frontend — SGE Workspace Portal

Folder ini berisi antarmuka yang dirender Laravel:

- `views/`: Blade templates untuk login, dashboard tiap role, anggota, iuran, tagihan, verifikasi pembayaran, transaksi, laporan, notifikasi, dan profil.
- `public/css/app.css`: styling responsif dengan warna teal, cream, sage, serta aksen kuning/lavender/peach.
- `public/js/app.js`: interaksi UI seperti modal dan navigasi.
- `public/images/`: aset banner SGE Workspace.

Frontend tidak dijalankan sebagai server terpisah. Laravel backend diarahkan untuk memakai `frontend/views` sebagai view path dan `frontend/public` sebagai public path. Jalankan aplikasi dari folder `backend` menggunakan `php artisan serve`.
