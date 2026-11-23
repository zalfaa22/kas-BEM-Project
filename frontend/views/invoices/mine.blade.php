
@extends('layouts.app')

@section('title', 'Tagihan Saya')
@section('crumb', 'Tagihan Saya')

@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">MY INVOICES</span>
        <h1>Tagihan saya</h1>
        <p>Pilih metode pembayaran, lalu unggah bukti pembayaran untuk diverifikasi Bendahara.</p>
    </div>
</div>

<div class="invoice-list">
    @forelse($invoices as $i)
        <section class="panel invoice-card">
            <div class="invoice-top">
                <div>
                    <span class="eyebrow">{{ $i->period_label }}</span>
                    <h3>{{ $i->member->name }}</h3>
                    <small>Jatuh tempo {{ $i->due_date->format('d M Y') }}</small>
                </div>
                <span class="status {{ $i->status }}">
                    {{ str_replace('_', ' ', ucfirst($i->status)) }}
                </span>
            </div>

            <div class="invoice-bottom">
                <div>
                    <small>Total tagihan</small>
                    <strong>Rp {{ number_format($i->amount, 0, ',', '.') }}</strong>
                </div>

                @if(in_array($i->status, ['belum_bayar', 'ditolak']))
                    <button class="btn primary" onclick="openModal('proof{{ $i->id }}')">
                        Bayar →
                    </button>
                @elseif($i->status === 'menunggu_verifikasi')
                    <span class="muted">Pembayaran sedang diperiksa Bendahara.</span>
                @else
                    <span class="green">✓ Pembayaran terverifikasi</span>
                @endif
            </div>
        </section>

        @if(in_array($i->status, ['belum_bayar', 'ditolak']))
            <div class="modal" id="proof{{ $i->id }}">
                <div class="modal-card">
                    <div class="modal-head">
                        <div>
                            <span class="eyebrow">PAYMENT SUBMISSION</span>
                            <h3>Pembayaran tagihan</h3>
                        </div>
                        <button class="close" onclick="closeModal('proof{{ $i->id }}')">×</button>
                    </div>

                    <p class="muted">
                        Total tagihan:
                        <strong>Rp {{ number_format($i->amount, 0, ',', '.') }}</strong>
                    </p>

                    <form method="POST"
                          enctype="multipart/form-data"
                          action="{{ route('invoices.proof', $i) }}"
                          class="form-stack">
                        @csrf

                        <label for="method">Metode Pembayaran</label>

<select name="method" id="method" required>
    <option value="">Pilih metode pembayaran</option>

    <optgroup label="Transfer Bank">
        <option value="transfer_bca">BCA</option>
        <option value="transfer_bri">BRI</option>
        <option value="transfer_bni">BNI</option>
        <option value="transfer_mandiri">Mandiri</option>
        <option value="transfer_bsi">BSI</option>
    </optgroup>

    <optgroup label="E-Wallet">
        <option value="ewallet_dana">DANA</option>
        <option value="ewallet_gopay">GoPay</option>
        <option value="ewallet_ovo">OVO</option>
        <option value="ewallet_shopeepay">ShopeePay</option>
        <option value="ewallet_linkaja">LinkAja</option>
    </optgroup>
</select>

                        <label>Tanggal pembayaran</label>
                        <input type="datetime-local" name="paid_at" required>

                        <label>Bukti pembayaran (JPG/PNG/PDF, maks. 2 MB)</label>
                        <input type="file"
                               name="proof"
                               accept=".jpg,.jpeg,.png,.pdf"
                               required>

                        <button type="submit" class="btn primary full">
                            Kirim untuk verifikasi →
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @empty
        <div class="panel empty">Belum ada tagihan.</div>
    @endforelse
</div>

<div class="pagination">{{ $invoices->links() }}</div>
@endsection
