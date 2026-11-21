@extends('layouts.app') @section('title','Tagihan Saya') @section('crumb','Tagihan Saya') @section('content')<div class="page-heading">
    <div><span class="eyebrow">MY INVOICES</span>
        <h1>Tagihan saya</h1>
        <p>Lakukan transfer di luar sistem, lalu unggah bukti untuk diverifikasi Bendahara.</p>
    </div>
</div>
<div class="invoice-list">@forelse($invoices as $i)<section class="panel invoice-card">
        <div class="invoice-top">
            <div><span class="eyebrow">{{ $i->period_label }}</span>
                <h3>{{ $i->member->name }}</h3><small>Jatuh tempo {{ $i->due_date->format('d M Y') }}</small>
            </div><span class="status {{ $i->status }}">{{ str_replace('_',' ',ucfirst($i->status)) }}</span>
        </div>
        <div class="invoice-bottom">
            <div><small>Total tagihan</small><strong>Rp {{ number_format($i->amount,0,',','.') }}</strong></div>@if(in_array($i->status,['belum_bayar','ditolak']))<button class="btn primary" onclick="openModal('proof{{ $i->id }}')">↑ Upload bukti</button>@elseif($i->status==='menunggu_verifikasi')<span class="muted">Bukti sedang diperiksa Bendahara.</span>@else<span class="green">✓ Pembayaran terverifikasi</span>@endif
        </div>
    </section>@if(in_array($i->status,['belum_bayar','ditolak']))<div class="modal" id="proof{{ $i->id }}">
        <div class="modal-card">
            <div class="modal-head">
                <div><span class="eyebrow">PAYMENT SUBMISSION</span>
                    <h3>Unggah bukti pembayaran</h3>
                </div><button class="close" onclick="closeModal('proof{{ $i->id }}')">×</button>
            </div>
            <p class="muted">Pastikan transfer sebesar Rp {{ number_format($i->amount,0,',','.') }} telah dilakukan melalui rekening organisasi.</p>
            <form method="POST" enctype="multipart/form-data" action="{{ route('invoices.proof',$i) }}" class="form-stack">@csrf<label>Tanggal pembayaran</label><input type="datetime-local" name="paid_at" required><label>Bukti pembayaran (JPG/PNG/PDF, maks. 2 MB)</label><input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required><button class="btn primary full">Kirim untuk verifikasi →</button></form>
        </div>
    </div>@endif @empty<div class="panel empty">Belum ada tagihan.</div>@endforelse</div>
<div class="pagination">{{ $invoices->links() }}</div>@endsection