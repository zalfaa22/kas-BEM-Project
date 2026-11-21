@extends('layouts.app') @section('title','Verifikasi Pembayaran') @section('crumb','Verifikasi Pembayaran') @section('content')<div class="page-heading">
    <div><span class="eyebrow">PAYMENT REVIEW</span>
        <h1>Verifikasi pembayaran</h1>
        <p>Periksa bukti transfer sebelum pemasukan dicatat ke kas organisasi.</p>
    </div><span class="pill">{{ $payments->total() }} pengajuan</span>
</div>
<div class="payment-grid">@forelse($payments as $p)<section class="panel payment-card">
        <div class="payment-card-top">
            <div class="person"><span class="avatar">{{ strtoupper(substr($p->member->name,0,1)) }}</span>
                <div><b>{{ $p->member->name }}</b><small>{{ $p->invoice->period_label }} · {{ $p->paid_at?->format('d M Y H:i') }}</small></div>
            </div><span class="status {{ $p->status==='diterima'?'paid':($p->status==='ditolak'?'rejected':'menunggu_verifikasi') }}">{{ str_replace('_',' ',ucfirst($p->status)) }}</span>
        </div>
        <div class="proof-preview">@if($p->proof && strtolower(pathinfo($p->proof->original_name,PATHINFO_EXTENSION))==='pdf')<a class="btn light" target="_blank" href="{{ Storage::disk('public')->url($p->proof->path_file) }}">Buka bukti PDF ↗</a>@else<img src="{{ $p->proof && str_starts_with($p->proof->path_file,'demo/') ? 'https://placehold.co/700x420/eaf2ed/1d7775?text=Demo+Payment+Proof' : ($p->proof ? Storage::disk('public')->url($p->proof->path_file) : '') }}" alt="Bukti pembayaran">@endif</div>
        <div class="payment-amount"><small>Nominal pembayaran</small><strong>Rp {{ number_format($p->amount,0,',','.') }}</strong></div>@if($p->status==='menunggu_verifikasi')<div class="review-actions">
            <form method="POST" action="{{ route('payments.verify',$p) }}">@csrf<input type="hidden" name="action" value="approve"><button class="btn primary full">✓ Terima pembayaran</button></form>
            <form method="POST" action="{{ route('payments.verify',$p) }}" class="reject-form">@csrf<input type="hidden" name="action" value="reject"><input name="reason" placeholder="Alasan penolakan (wajib)" required><button class="btn danger full">Tolak bukti</button></form>
        </div>@else<small class="muted">{{ $p->status==='ditolak'?'Alasan: '.$p->rejection_reason:'Diverifikasi pada '.$p->verified_at?->format('d M Y') }}</small>@endif
    </section>@empty<div class="panel empty">Belum ada pengajuan pembayaran.</div>@endforelse</div>
<div class="pagination">{{ $payments->links() }}</div>@endsection