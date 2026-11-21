@extends('layouts.app') @section('title','Laporan Keuangan') @section('crumb','Laporan') @section('content')<div class="page-heading">
    <div><span class="eyebrow">FINANCIAL REPORT</span>
        <h1>Laporan keuangan</h1>
        <p>Ringkasan pemasukan, pengeluaran, dan saldo berdasarkan transaksi tercatat.</p>
    </div>
    <div class="welcome-actions"><button class="btn light" onclick="window.print()">⎙ Print / Save PDF</button><a class="btn primary" href="{{ route('reports.export') }}">↧ Export CSV</a></div>
</div>
<div class="stats-grid three">
    <div class="stat-card">
        <div class="stat-top"><span>Total pemasukan</span><span class="stat-icon mint">↙</span></div><strong>Rp {{ number_format($income,0,',','.') }}</strong><small>Termasuk pembayaran terverifikasi</small>
    </div>
    <div class="stat-card">
        <div class="stat-top"><span>Total pengeluaran</span><span class="stat-icon peach">↗</span></div><strong>Rp {{ number_format($expense,0,',','.') }}</strong><small>Dicatat Bendahara</small>
    </div>
    <div class="stat-card dark-stat">
        <div class="stat-top"><span>Saldo akhir</span><span>✳</span></div><strong>Rp {{ number_format($income-$expense,0,',','.') }}</strong><small>Pemasukan dikurangi pengeluaran</small>
    </div>
</div>
<section class="panel table-panel">
    <div class="panel-head">
        <div><span class="eyebrow">TRANSACTION DETAIL</span>
            <h3>Rincian transaksi</h3>
        </div><span class="pill">{{ $rows->count() }} baris</span>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>TANGGAL</th>
                    <th>DESKRIPSI</th>
                    <th>KATEGORI</th>
                    <th>PEMASUKAN</th>
                    <th>PENGELUARAN</th>
                </tr>
            </thead>
            <tbody>@foreach($rows as $t)<tr>
                    <td>{{ $t->transaction_date->format('d M Y') }}</td>
                    <td><b>{{ $t->description }}</b></td>
                    <td>{{ $t->category }}</td>
                    <td class="green">{{ $t->type==='pemasukan'?'Rp '.number_format($t->amount,0,',','.'): '—' }}</td>
                    <td class="red">{{ $t->type==='pengeluaran'?'Rp '.number_format($t->amount,0,',','.'): '—' }}</td>
                </tr>@endforeach</tbody>
        </table>
    </div>
</section>@endsection