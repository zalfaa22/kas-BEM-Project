<?php

namespace Tests\Feature;

use App\Models\CashTransaction;
use App\Models\DuesRule;
use App\Models\Invoice;
use App\Models\Notice;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KasBemWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Tampilan berada di folder frontend/views.
        $frontendViews = base_path('../frontend/views');

        if (is_dir($frontendViews)) {
            config(['view.paths' => [$frontendViews]]);
        }
    }

    // Membuat data pengguna untuk kebutuhan pengujian.
    private function buatPengguna(string $role = 'anggota', array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => ucfirst($role) . ' Test',
            'email' => $role . uniqid() . '@test.local',
            'password' => Hash::make('password123'),
            'role' => $role,
            'status_anggota' => 'aktif',
        ], $attributes));
    }

    // Membuat data tagihan untuk kebutuhan pengujian.
    private function buatTagihan(User $member, array $attributes = []): Invoice
    {
        return Invoice::create(array_merge([
            'member_id' => $member->id,
            'period_label' => 'November 2026',
            'amount' => 25000,
            'due_date' => '2026-11-30',
            'status' => 'belum_bayar',
        ], $attributes));
    }

    // Membuat data pembayaran untuk kebutuhan pengujian.
    private function buatPembayaran(Invoice $invoice, User $member, array $attributes = []): Payment
    {
        return Payment::create(array_merge([
            'invoice_id' => $invoice->id,
            'member_id' => $member->id,
            'amount' => $invoice->amount,
            'paid_at' => '2026-11-24 10:00:00',
            'method' => 'transfer_bca',
            'status' => 'menunggu_verifikasi',
        ], $attributes));
    }

    // ==========================================
    // 1. PENGUJIAN LOGIN
    // ==========================================

    public function test_halaman_login_dapat_dibuka(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_pengguna_dapat_login_dengan_data_yang_valid(): void
    {
        $user = $this->buatPengguna('anggota', [
            'email' => 'anggota-login@test.local'
        ]);

        $this->post('/login', [
            'email' => 'anggota-login@test.local',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_ditolak_jika_kata_sandi_salah(): void
    {
        $this->buatPengguna('anggota', [
            'email' => 'wrong-pass@test.local'
        ]);

        $this->from('/login')->post('/login', [
            'email' => 'wrong-pass@test.local',
            'password' => 'incorrect',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_pengguna_dapat_logout_dan_mengakhiri_sesi(): void
    {
        $user = $this->buatPengguna();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    // ==========================================
    // 2. PENGELOLAAN DATA ANGGOTA
    // ==========================================

    public function test_bendahara_dapat_membuka_halaman_anggota(): void
    {
        $this->actingAs($this->buatPengguna('bendahara'))
            ->get(route('members'))
            ->assertOk();
    }

    public function test_anggota_tidak_dapat_membuka_halaman_pengelolaan_anggota(): void
    {
        $this->actingAs($this->buatPengguna('anggota'))
            ->get(route('members'))
            ->assertForbidden();
    }

    public function test_bendahara_dapat_menambahkan_anggota_baru(): void
    {
        $this->actingAs($this->buatPengguna('bendahara'))
            ->post(route('members.store'), [
                'name' => 'Anggota Baru',
                'nim' => '245150600111001',
                'email' => 'anggota-baru@test.local',
                'password' => 'password123',
                'status_anggota' => 'aktif',
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Anggota Baru',
            'email' => 'anggota-baru@test.local',
            'role' => 'anggota',
            'status_anggota' => 'aktif',
        ]);
    }

    public function test_penambahan_anggota_ditolak_jika_email_sudah_digunakan(): void
    {
        $this->buatPengguna('anggota', [
            'email' => 'duplicate@test.local'
        ]);

        $this->actingAs($this->buatPengguna('bendahara'))
            ->from('/members')
            ->post(route('members.store'), [
                'name' => 'Duplikat',
                'email' => 'duplicate@test.local',
                'password' => 'password123',
                'status_anggota' => 'aktif',
            ])->assertSessionHasErrors('email');
    }

    // ==========================================
    // 3. PENGATURAN IURAN DAN PENERBITAN TAGIHAN
    // ==========================================

    public function test_bendahara_dapat_membuat_aturan_iuran(): void
    {
        $this->actingAs($this->buatPengguna('bendahara'))
            ->post(route('dues.store'), [
                'name' => 'Iuran Bulanan',
                'period' => 'bulanan',
                'amount' => 25000,
                'due_day' => 10,
                'description' => 'Iuran rutin organisasi',
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('dues_rules', [
            'name' => 'Iuran Bulanan',
            'period' => 'bulanan',
            'amount' => 25000,
            'active' => true,
        ]);
    }

    public function test_aturan_iuran_menolak_nominal_nol_dan_tanggal_jatuh_tempo_tidak_valid(): void
    {
        $this->actingAs($this->buatPengguna('bendahara'))
            ->from('/dues')
            ->post(route('dues.store'), [
                'name' => 'Iuran Tidak Valid',
                'period' => 'bulanan',
                'amount' => 0,
                'due_day' => 29,
            ])->assertSessionHasErrors(['amount', 'due_day']);
    }

    public function test_bendahara_dapat_menerbitkan_tagihan_kepada_seluruh_anggota_aktif(): void
    {
        $bendahara = $this->buatPengguna('bendahara');
        $ketua = $this->buatPengguna('ketua');
        $anggota = $this->buatPengguna('anggota');

        $anggotaNonaktif = $this->buatPengguna('anggota', [
            'status_anggota' => 'nonaktif'
        ]);

        $aturanIuran = DuesRule::create([
            'name' => 'Iuran Bulanan',
            'period' => 'bulanan',
            'amount' => 25000,
            'due_day' => 10,
            'active' => true,
        ]);

        $this->actingAs($bendahara)
            ->post(route('invoices.generate'), [
                'dues_rule_id' => $aturanIuran->id,
                'period_label' => 'November 2026',
                'due_date' => '2026-11-30',
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('invoices', [
            'member_id' => $bendahara->id,
            'period_label' => 'November 2026'
        ]);

        $this->assertDatabaseHas('invoices', [
            'member_id' => $ketua->id,
            'period_label' => 'November 2026'
        ]);

        $this->assertDatabaseHas('invoices', [
            'member_id' => $anggota->id,
            'period_label' => 'November 2026'
        ]);

        $this->assertDatabaseMissing('invoices', [
            'member_id' => $anggotaNonaktif->id,
            'period_label' => 'November 2026'
        ]);
    }

    // ==========================================
    // 4. MELIHAT TAGIHAN MILIK SENDIRI
    // ==========================================

    public function test_pengguna_hanya_dapat_melihat_tagihan_miliknya_sendiri(): void
    {
        $anggota = $this->buatPengguna('anggota');
        $anggotaLain = $this->buatPengguna('anggota');

        $tagihanSaya = $this->buatTagihan($anggota, [
            'period_label' => 'Tagihan Saya'
        ]);

        $this->buatTagihan($anggotaLain, [
            'period_label' => 'Tagihan Orang Lain'
        ]);

        $response = $this->actingAs($anggota)
            ->get(route('my-invoices'))
            ->assertOk();

        $response->assertSee('Tagihan Saya')
            ->assertDontSee('Tagihan Orang Lain');

        $this->assertSame($anggota->id, $tagihanSaya->member_id);
    }

    // ==========================================
    // 5. PENGAJUAN PEMBAYARAN IURAN
    // ==========================================

    public function test_anggota_dapat_mengirim_bukti_pembayaran_yang_valid(): void
    {
        Storage::fake('public');

        $anggota = $this->buatPengguna('anggota');
        $tagihan = $this->buatTagihan($anggota);

        $this->actingAs($anggota)
            ->post(route('invoices.proof', $tagihan), [
                'method' => 'transfer_bca',
                'paid_at' => '2026-11-24 10:00:00',
                'proof' => UploadedFile::fake()->create(
                    'bukti.pdf',
                    100,
                    'application/pdf'
                ),
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $tagihan->id,
            'member_id' => $anggota->id,
            'status' => 'menunggu_verifikasi',
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $tagihan->id,
            'status' => 'menunggu_verifikasi',
        ]);

        $this->assertDatabaseCount('payment_proofs', 1);
    }

    public function test_anggota_tidak_dapat_mengirim_bukti_pembayaran_untuk_tagihan_orang_lain(): void
    {
        Storage::fake('public');

        $pemilikTagihan = $this->buatPengguna('anggota');
        $anggotaLain = $this->buatPengguna('anggota');

        $tagihan = $this->buatTagihan($pemilikTagihan);

        $this->actingAs($anggotaLain)
            ->post(route('invoices.proof', $tagihan), [
                'paid_at' => '2026-11-24 10:00:00',
                'proof' => UploadedFile::fake()->create(
                    'bukti.pdf',
                    100,
                    'application/pdf'
                ),
            ])->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_pengajuan_pembayaran_ditolak_jika_file_tidak_valid_dan_tanggal_kosong(): void
    {
        Storage::fake('public');

        $anggota = $this->buatPengguna('anggota');
        $tagihan = $this->buatTagihan($anggota);

        $this->actingAs($anggota)
            ->from('/my-invoices')
            ->post(route('invoices.proof', $tagihan), [
                'method' => 'transfer_bca',
                'proof' => UploadedFile::fake()->create(
                    'bukti.txt',
                    10,
                    'text/plain'
                ),
            ])->assertSessionHasErrors(['proof', 'paid_at']);

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_pembayaran_tidak_dapat_diajukan_untuk_tagihan_yang_sudah_lunas_atau_menunggu_verifikasi(): void
    {
        Storage::fake('public');

        $anggota = $this->buatPengguna('anggota');

        $tagihan = $this->buatTagihan($anggota, [
            'status' => 'lunas'
        ]);

        $this->actingAs($anggota)
            ->post(route('invoices.proof', $tagihan), [
                'paid_at' => '2026-11-24 10:00:00',
                'proof' => UploadedFile::fake()->create(
                    'bukti.pdf',
                    100,
                    'application/pdf'
                ),
            ])->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 0);
    }

    // ==========================================
    // 6. MELIHAT DAFTAR PENGAJUAN PEMBAYARAN
    // ==========================================

    public function test_bendahara_dapat_melihat_daftar_pengajuan_pembayaran(): void
    {
        $anggota = $this->buatPengguna('anggota');
        $bendahara = $this->buatPengguna('bendahara');

        $tagihan = $this->buatTagihan($anggota, [
            'status' => 'menunggu_verifikasi'
        ]);

        $this->buatPembayaran($tagihan, $anggota);

        $this->actingAs($bendahara)
            ->get(route('payments'))
            ->assertOk();
    }

    public function test_anggota_tidak_dapat_membuka_halaman_verifikasi_pembayaran(): void
    {
        $this->actingAs($this->buatPengguna('anggota'))
            ->get(route('payments'))
            ->assertForbidden();
    }

    // ==========================================
    // 7. VERIFIKASI PEMBAYARAN
    // ==========================================

    public function test_bendahara_dapat_menyetujui_pembayaran_dan_mencatat_pemasukan_kas(): void
    {
        $anggota = $this->buatPengguna('anggota');
        $bendahara = $this->buatPengguna('bendahara');

        $tagihan = $this->buatTagihan($anggota, [
            'status' => 'menunggu_verifikasi'
        ]);

        $pembayaran = $this->buatPembayaran($tagihan, $anggota);

        $this->actingAs($bendahara)
            ->post(route('payments.verify', $pembayaran), [
                'action' => 'approve',
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'id' => $pembayaran->id,
            'status' => 'diterima',
            'verified_by' => $bendahara->id,
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $tagihan->id,
            'status' => 'lunas'
        ]);

        $this->assertDatabaseHas('cash_transactions', [
            'payment_id' => $pembayaran->id,
            'type' => 'pemasukan',
            'amount' => 25000,
        ]);

        $this->assertDatabaseHas('notices', [
            'user_id' => $anggota->id,
            'title' => 'Pembayaran diterima'
        ]);
    }

    public function test_bendahara_dapat_menolak_pembayaran_dan_menyimpan_alasan_penolakan(): void
    {
        $anggota = $this->buatPengguna('anggota');
        $bendahara = $this->buatPengguna('bendahara');

        $tagihan = $this->buatTagihan($anggota, [
            'status' => 'menunggu_verifikasi'
        ]);

        $pembayaran = $this->buatPembayaran($tagihan, $anggota);

        $this->actingAs($bendahara)
            ->post(route('payments.verify', $pembayaran), [
                'action' => 'reject',
                'reason' => 'Bukti transfer tidak terbaca',
            ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'id' => $pembayaran->id,
            'status' => 'ditolak',
            'rejection_reason' => 'Bukti transfer tidak terbaca',
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $tagihan->id,
            'status' => 'ditolak'
        ]);

        $this->assertDatabaseHas('notices', [
            'user_id' => $anggota->id,
            'title' => 'Pembayaran perlu diperbaiki'
        ]);

        $this->assertDatabaseCount('cash_transactions', 0);
    }

    public function test_pembayaran_yang_sudah_diproses_tidak_dapat_diverifikasi_ulang(): void
    {
        $anggota = $this->buatPengguna('anggota');
        $bendahara = $this->buatPengguna('bendahara');

        $tagihan = $this->buatTagihan($anggota, [
            'status' => 'lunas'
        ]);

        $pembayaran = $this->buatPembayaran($tagihan, $anggota, [
            'status' => 'diterima'
        ]);

        $this->actingAs($bendahara)
            ->from('/payments')
            ->post(route('payments.verify', $pembayaran), [
                'action' => 'reject',
                'reason' => 'Percobaan verifikasi ulang',
            ])->assertSessionHas('error');

        $this->assertDatabaseHas('payments', [
            'id' => $pembayaran->id,
            'status' => 'diterima'
        ]);
    }
}
