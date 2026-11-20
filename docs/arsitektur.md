# Arsitektur dan Model Data

## Arsitektur aplikasi

```mermaid
flowchart TD
    U[Browser Pengguna] --> F[Frontend: Blade, CSS, JavaScript]
    F --> R[Laravel Routes + Middleware]
    R --> C[Controllers / Business Logic]
    C --> M[Eloquent Models]
    M --> DB[(Supabase PostgreSQL)]
    C --> FS[Local Storage untuk bukti demo]
```

## Pemetaan entitas terhadap dokumen Modul 2

| Entitas dokumen | Implementasi | Keterangan |
|---|---|---|
| Departemen | `departments` | Master departemen anggota |
| AnggotaBEM | `users` | Data anggota + kredensial + role + status anggota |
| AturanIuran | `dues_rules` | Nominal, frekuensi, tanggal jatuh tempo, aktif/nonaktif |
| Tagihan | `invoices` | Tagihan per anggota dan periode |
| TransaksiKas | `payments` + `cash_transactions` | Pengajuan pembayaran dan buku kas masuk/keluar |
| BuktiPembayaran | `payment_proofs` | Metadata file bukti yang terpisah dari transaksi |

## ERD implementasi

```mermaid
erDiagram
    DEPARTMENTS ||--o{ USERS : menaungi
    USERS ||--o{ INVOICES : menerima
    DUES_RULES ||--o{ INVOICES : menjadi_acuan
    INVOICES ||--o{ PAYMENTS : memiliki_riwayat_pengajuan
    USERS ||--o{ PAYMENTS : mengajukan
    PAYMENTS ||--o| PAYMENT_PROOFS : dilengkapi
    PAYMENTS ||--o| CASH_TRANSACTIONS : menghasilkan_pemasukan
    USERS ||--o{ CASH_TRANSACTIONS : mencatat
    USERS ||--o{ NOTICES : menerima
    INVOICES ||--o{ NOTICES : terkait

    DEPARTMENTS { bigint id PK }
    USERS { bigint id PK bigint department_id FK string role string status_anggota }
    DUES_RULES { bigint id PK decimal amount string period date due_day }
    INVOICES { bigint id PK bigint member_id FK bigint dues_rule_id FK string period_label decimal amount date due_date string status }
    PAYMENTS { bigint id PK bigint invoice_id FK bigint member_id FK decimal amount string status bigint verified_by FK }
    PAYMENT_PROOFS { bigint id PK bigint payment_id FK string original_name string path_file integer file_size }
    CASH_TRANSACTIONS { bigint id PK bigint payment_id FK string type decimal amount date transaction_date }
    NOTICES { bigint id PK bigint user_id FK bigint invoice_id FK string title }
```

`payments` dapat memiliki lebih dari satu pengajuan untuk satu tagihan karena anggota perlu dapat mengunggah ulang setelah penolakan. Hanya pembayaran yang diterima menghasilkan satu baris pemasukan; kolom `cash_transactions.payment_id` memiliki unique constraint.

## Status pembayaran

```mermaid
stateDiagram-v2
    [*] --> belum_bayar
    belum_bayar --> menunggu_verifikasi: anggota unggah bukti
    menunggu_verifikasi --> lunas: bendahara menerima
    menunggu_verifikasi --> ditolak: bendahara menolak
    ditolak --> menunggu_verifikasi: anggota unggah ulang
```
