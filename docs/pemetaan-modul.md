# Pemetaan Modul dan Aktor

Pemetaan berikut mengacu pada User Story, User Requirement, System Requirement, use case scenario, class/data model, dan pseudo-code dalam dokumen kelompok.

| Area / skenario | Implementasi | Aktor |
|---|---|---|
| Login dan otorisasi | `AuthController`, `RoleMiddleware` | Bendahara, Anggota, Ketua BEM |
| Kelola data anggota | `PortalController::members/storeMember/updateMember` | Bendahara |
| Konfigurasi aturan iuran | `dues`, `storeDues`, `toggleDues` | Bendahara |
| Generate tagihan massal | `generate` | Bendahara + sistem |
| Melihat tagihan pribadi | `myInvoices` | Anggota, Bendahara sebagai anggota |
| Upload bukti transfer | `uploadProof` | Anggota, Bendahara sebagai anggota |
| Verifikasi bukti | `payments`, `verify` | Bendahara |
| Update status dan saldo | `verify` + transaksi DB | Sistem setelah aksi Bendahara |
| Monitoring tunggakan | `reminders`, dashboard | Bendahara, Anggota untuk data sendiri, Ketua BEM untuk ringkasan |
| Riwayat pembayaran | Daftar tagihan dan pembayaran pribadi | Anggota |
| Monitoring kas | `monitoring` | Ketua BEM |
| Pengeluaran dan laporan | `storeExpense`, `reports`, `export` | Bendahara |
| Notifikasi | `notices`, `readNotice` | Pengguna sesuai akun |

## Pembatasan akses

- Role `bendahara`: operasional administrasi keuangan dan anggota; juga dapat mengakses fitur tagihan pribadi.
- Role `anggota`: query tagihan dibatasi dengan `member_id` akun login.
- Role `ketua`: hanya endpoint monitoring read-only.
- Role Admin pada deskripsi umum dokumen tidak dibuat sebagai akun terpisah pada implementasi ini; kewenangan pengelolaan sistem yang diminta ditempatkan pada Bendahara mengikuti kebutuhan implementasi pengguna dan fokus use case Modul 2.

## Catatan konsistensi laporan

Dokumen awal menyebut DBMS MySQL. Implementasi yang disiapkan memakai Supabase PostgreSQL. Jika tim memutuskan memakai starter ini, revisi bagian lingkungan operasi dan keputusan teknologi di laporan. Relasi, aktor, proses bisnis, status, dan alur pembayaran inti tetap dipertahankan.
