<?php

namespace App\Http\Controllers;

use App\Models\{
    User,
    Department,
    DuesRule,
    Invoice,
    Payment,
    PaymentProof,
    CashTransaction,
    Notice
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalController extends Controller
{
    public function dashboard()
{
    $u = request()->user();

    $income = CashTransaction::where('type', 'pemasukan')->sum('amount');
    $expense = CashTransaction::where('type', 'pengeluaran')->sum('amount');

    $data = [
        'balance' => $income - $expense,
        'income' => $income,
        'expense' => $expense,
        'members' => User::where('role', 'anggota')->where('status_anggota', 'aktif')->count(),
        'unpaid' => Invoice::whereIn('status', ['belum_bayar', 'ditolak'])->count(),
        'pending' => Payment::where('status', 'menunggu_verifikasi')->count(),
        'paid' => Invoice::where('status', 'lunas')->count(),
        'recent' => CashTransaction::with('payment')->latest('transaction_date')->take(6)->get(),
        'myInvoices' => Invoice::where('member_id', $u->id)->latest()->take(5)->get(),
        'myPayments' => Payment::with(['invoice', 'proof'])->where('member_id', $u->id)->latest()->take(5)->get(),
        'notices' => Notice::where('user_id', $u->id)->latest()->take(5)->get()
    ];

    if ($u->role === 'anggota') {
        return view('dashboard.member', $data);
    }

    if ($u->role === 'ketua') {
        return view('dashboard.president', $data);
    }

    return view('dashboard.index', $data);
}

    public function members(Request $r)
    {
        $q = User::with('department')
            ->whereIn('role', ['anggota', 'bendahara', 'ketua']);

        if ($r->filled('q')) {
            $keyword = trim($r->q);

            $q->where(function ($x) use ($keyword) {
                $x->whereRaw('LOWER(name) LIKE LOWER(?)', ['%' . $keyword . '%'])
                    ->orWhereRaw('LOWER(nim) LIKE LOWER(?)', ['%' . $keyword . '%']);
            });
        }

        if ($r->filled('status')) {
            $q->where('status_anggota', $r->status);
        }

        return view('members.index', [
            'members' => $q->orderBy('name')->paginate(10)->withQueryString(),
            'departments' => Department::all()
        ]);
    }

    public function storeMember(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|max:100',
            'nim' => 'nullable|max:30|unique:users,nim',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'department_id' => 'nullable|exists:departments,id',
            'status_anggota' => 'required|in:aktif,nonaktif'
        ]);

        User::create($d + ['role' => 'anggota']);

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function updateMember(Request $r, User $member)
    {
        $d = $r->validate([
            'name' => 'required|max:100',
            'nim' => 'nullable|max:30|unique:users,nim,' . $member->id,
            'email' => 'required|email|unique:users,email,' . $member->id,
            'department_id' => 'nullable|exists:departments,id',
            'status_anggota' => 'required|in:aktif,nonaktif'
        ]);

        $member->update($d);

        return back()->with('success', 'Data anggota diperbarui.');
    }

    public function dues()
    {
        return view('dues.index', [
            'rules' => DuesRule::latest()->get()
        ]);
    }

    public function storeDues(Request $r)
    {
        $d = $r->validate([
            'name' => 'required',
            'period' => 'required|in:mingguan,bulanan,tahunan',
            'amount' => 'required|numeric|min:1',
            'due_day' => 'required|integer|min:1|max:28',
            'description' => 'nullable'
        ]);

        DuesRule::create($d + ['active' => true]);

        return back()->with('success', 'Aturan iuran disimpan.');
    }

    public function toggleDues(DuesRule $rule)
    {
        $rule->update(['active' => !$rule->active]);

        return back()->with(
            'success',
            $rule->active ? 'Aturan iuran diaktifkan.' : 'Aturan iuran dinonaktifkan.'
        );
    }

    public function generate(Request $r)
    {
        $d = $r->validate([
            'dues_rule_id' => 'required|exists:dues_rules,id',
            'period_label' => 'required|max:80',
            'due_date' => 'required|date'
        ]);

        $rule = DuesRule::where('active', true)->findOrFail($d['dues_rule_id']);

        $members = User::where('status_anggota', 'aktif')
            ->whereIn('role', ['anggota', 'bendahara', 'ketua'])
            ->get();

        if (!$members->count()) {
            return back()->with('error', 'Tidak ada anggota aktif ditemukan.');
        }

        $created = 0;

        foreach ($members as $m) {
            $inv = Invoice::firstOrCreate(
                [
                    'member_id' => $m->id,
                    'period_label' => $d['period_label']
                ],
                [
                    'dues_rule_id' => $rule->id,
                    'amount' => $rule->amount,
                    'due_date' => $d['due_date'],
                    'status' => 'belum_bayar'
                ]
            );

            if ($inv->wasRecentlyCreated) {
                $created++;

                Notice::create([
                    'user_id' => $m->id,
                    'invoice_id' => $inv->id,
                    'title' => 'Tagihan iuran baru',
                    'body' => 'Tagihan ' . $d['period_label'] . ' sebesar Rp ' .
                        number_format($rule->amount, 0, ',', '.') . ' telah diterbitkan.'
                ]);
            }
        }

        return back()->with(
            'success',
            "{$created} tagihan berhasil dibuat untuk anggota aktif. Tagihan duplikat dilewati."
        );
    }

    public function invoices(Request $r)
    {
        $q = Invoice::with('member')->latest();

        if ($r->status) {
            $q->where('status', $r->status);
        }

        return view('invoices.index', [
            'invoices' => $q->paginate(12)->withQueryString(),
            'rules' => DuesRule::where('active', true)->latest()->get()
        ]);
    }

    public function myInvoices()
    {
        return view('invoices.mine', [
            'invoices' => Invoice::where('member_id', request()->user()->id)
                ->latest()
                ->paginate(12)
        ]);
    }

    public function uploadProof(Request $r, Invoice $invoice)
    {
        abort_unless($invoice->member_id === $r->user()->id, 403);

        if (!in_array($invoice->status, ['belum_bayar', 'ditolak'])) {
            return back()->with('error', 'Tagihan ini tidak dapat diajukan ulang.');
        }

        $d = $r->validate([
            'method' => 'required|in:transfer_bca,transfer_bri,transfer_bni,transfer_mandiri,transfer_bsi,ewallet_dana,ewallet_gopay,ewallet_ovo,ewallet_shopeepay,ewallet_linkaja',
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'paid_at' => 'required|date'
        ]);

        DB::transaction(function () use ($r, $invoice, $d) {
            $path = $r->file('proof')->store('payment-proofs', 'public');

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'member_id' => $r->user()->id,
                'amount' => $invoice->amount,
                'paid_at' => $d['paid_at'],
                'method' => $d['method'],
                'status' => 'menunggu_verifikasi'
            ]);

            PaymentProof::create([
                'original_name' => $r->file('proof')->getClientOriginalName(),
                'path_file' => $path,
                'file_size' => $r->file('proof')->getSize(),
                'uploaded_at' => now(),
                'payment_id' => $payment->id
            ]);

            $invoice->update(['status' => 'menunggu_verifikasi']);
        });

        return back()->with(
            'success',
            'Bukti pembayaran berhasil dikirim dan menunggu verifikasi Bendahara.'
        );
    }

    public function payments()
    {
        return view('payments.index', [
            'payments' => Payment::with(['member', 'invoice', 'proof'])
                ->latest()
                ->paginate(12)
        ]);
    }

    public function verify(Request $r, Payment $payment)
    {
        $d = $r->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($payment->status !== 'menunggu_verifikasi') {
            return back()->with('error', 'Pembayaran sudah diproses.');
        }

        DB::transaction(function () use ($r, $payment, $d) {
            $payment = Payment::whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->status !== 'menunggu_verifikasi') {
                return;
            }

            if ($d['action'] === 'approve') {
                $payment->update([
                    'status' => 'diterima',
                    'verified_by' => $r->user()->id,
                    'verified_at' => now()
                ]);

                $payment->invoice->update(['status' => 'lunas']);

                CashTransaction::firstOrCreate(
                    ['payment_id' => $payment->id],
                    [
                        'type' => 'pemasukan',
                        'category' => 'Iuran anggota',
                        'description' => 'Pembayaran ' . $payment->member->name .
                            ' - ' . $payment->invoice->period_label,
                        'amount' => $payment->amount,
                        'transaction_date' => now()->toDateString(),
                        'created_by' => $r->user()->id
                    ]
                );

                Notice::create([
                    'user_id' => $payment->member_id,
                    'invoice_id' => $payment->invoice_id,
                    'title' => 'Pembayaran diterima',
                    'body' => 'Pembayaran ' . $payment->invoice->period_label .
                        ' telah diverifikasi dan dinyatakan lunas.'
                ]);
            } else {
                $payment->update([
                    'status' => 'ditolak',
                    'rejection_reason' => $d['reason'] ?? 'Bukti pembayaran tidak sesuai',
                    'verified_by' => $r->user()->id,
                    'verified_at' => now()
                ]);

                $payment->invoice->update(['status' => 'ditolak']);

                Notice::create([
                    'user_id' => $payment->member_id,
                    'invoice_id' => $payment->invoice_id,
                    'title' => 'Pembayaran perlu diperbaiki',
                    'body' => 'Pembayaran ditolak: ' .
                        ($d['reason'] ?? 'Bukti tidak sesuai') .
                        '. Silakan unggah ulang bukti.'
                ]);
            }
        });

        return back()->with(
            'success',
            $d['action'] === 'approve'
                ? 'Pembayaran diverifikasi. Pemasukan kas telah dicatat.'
                : 'Pembayaran ditolak dan anggota diberi notifikasi.'
        );
    }

    public function transactions()
    {
        return view('transactions.index', [
            'transactions' => CashTransaction::latest('transaction_date')->paginate(15)
        ]);
    }

    public function storeExpense(Request $r)
    {
        $d = $r->validate([
            'category' => 'required|max:80',
            'description' => 'required|max:255',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date'
        ]);

        CashTransaction::create($d + [
            'type' => 'pengeluaran',
            'created_by' => $r->user()->id
        ]);

        return back()->with('success', 'Pengeluaran berhasil dicatat.');
    }

    public function reports()
    {
        $rows = CashTransaction::orderByDesc('transaction_date')->get();

        return view('reports.index', [
            'rows' => $rows,
            'income' => $rows->where('type', 'pemasukan')->sum('amount'),
            'expense' => $rows->where('type', 'pengeluaran')->sum('amount')
        ]);
    }

    public function monitoring()
    {
        $rows = CashTransaction::orderByDesc('transaction_date')->take(15)->get();

        $income = CashTransaction::where('type', 'pemasukan')->sum('amount');
        $expense = CashTransaction::where('type', 'pengeluaran')->sum('amount');

        return view('dashboard.president', [
            'balance' => $income - $expense,
            'income' => $income,
            'expense' => $expense,
            'members' => User::where('role', 'anggota')
                ->where('status_anggota', 'aktif')
                ->count(),
            'unpaid' => Invoice::whereIn('status', ['belum_bayar', 'ditolak'])->count(),
            'pending' => Payment::where('status', 'menunggu_verifikasi')->count(),
            'paid' => Invoice::where('status', 'lunas')->count(),
            'recent' => $rows
        ]);
    }

    public function export()
    {
        $rows = CashTransaction::orderBy('transaction_date')->get();

        return response()->streamDownload(function () use ($rows) {
            $o = fopen('php://output', 'w');

            fputcsv($o, ['Tanggal', 'Jenis', 'Kategori', 'Deskripsi', 'Nominal']);

            foreach ($rows as $x) {
                fputcsv($o, [
                    $x->transaction_date->format('Y-m-d'),
                    $x->type,
                    $x->category,
                    $x->description,
                    $x->amount
                ]);
            }

            fclose($o);
        }, 'laporan-kas-bem.csv', ['Content-Type' => 'text/csv']);
    }

    public function reminders()
    {
        $u = request()->user();

        $q = Invoice::with('member')
            ->whereIn('status', ['belum_bayar', 'ditolak'])
            ->whereDate('due_date', '<=', now()->addDays(7));

        if ($u->role === 'anggota') {
            $q->where('member_id', $u->id);
        }

        return view('dashboard.reminders', [
            'items' => $q->orderBy('due_date')->get()
        ]);
    }

    public function notices()
    {
        return view('dashboard.notices', [
            'items' => Notice::where('user_id', request()->user()->id)
                ->latest()
                ->paginate(15)
        ]);
    }

    public function readNotice(Notice $notice)
    {
        abort_unless($notice->user_id === request()->user()->id, 403);

        $notice->update(['read_at' => now()]);

        return back();
    }

    public function profile()
    {
        return view('profile.index');
    }

    public function updateProfile(Request $r)
    {
        $d = $r->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|unique:users,email,' . $r->user()->id,
            'password' => 'nullable|min:8'
        ]);

        $r->user()->update(array_filter($d));

        return back()->with('success', 'Profil diperbarui.');
    }
}
