@extends('layouts.app') @section('title','Overview') @section('crumb','Overview') @section('content')<div class="welcome-row">
    <div><span class="eyebrow">TUESDAY, {{ strtoupper(now()->format('d M Y')) }}</span>
        <h1>Good day, {{ explode(' ',auth()->user()->name)[0] }} <span class="wave">✳</span></h1>
        <p>Berikut ringkasan kondisi kas BEM FILKOM UB saat ini.</p>
    </div>
    <div class="welcome-actions"><a class="btn light" href="{{ route('reports') }}">↧ Lihat laporan</a><a class="btn primary" href="{{ route('dues') }}">＋ Atur iuran</a></div>
</div>
<div class="hero-banner">
    <div><span class="eyebrow">FINANCIAL SNAPSHOT · NOVEMBER 2026</span>
        <h2>Small steps, <em>stronger</em><br>financial clarity.</h2>
        <p>Semua transaksi tercatat. Semua keputusan lebih terarah.</p>
    </div>
    <div class="hero-orbit">
        <div class="orbit-ring"></div>
        <div class="orbit-core">✳</div><span class="orbit-tag">BEM<br>FILKOM</span>
    </div>
    <div class="hero-pattern"></div>
</div>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-top"><span>Total saldo kas</span><span class="stat-icon mint">◈</span></div><strong>Rp {{ number_format($balance,0,',','.') }}</strong><small class="trend positive">↗ Keuangan tercatat</small>
    </div>
    <div class="stat-card">
        <div class="stat-top"><span>Total pemasukan</span><span class="stat-icon yellow">↙</span></div><strong>Rp {{ number_format($income,0,',','.') }}</strong><small>Transaksi terverifikasi</small>
    </div>
    <div class="stat-card">
        <div class="stat-top"><span>Anggota aktif</span><span class="stat-icon lilac">♧</span></div><strong>{{ $members }}</strong><small>Terdaftar dalam sistem</small>
    </div>
    <div class="stat-card">
        <div class="stat-top"><span>Menunggu verifikasi</span><span class="stat-icon peach">◷</span></div><strong>{{ $pending }}</strong><small><a href="{{ route('payments') }}">Perlu ditinjau →</a></small>
    </div>
</div>
<div class="dashboard-grid">
    <section class="panel chart-panel">
        <div class="panel-head">
            <div><span class="eyebrow">CASH FLOW</span>
                <h3>Ringkasan keuangan</h3>
            </div><span class="pill">All time</span>
        </div>
        <div class="chart-summary">
            <div><small>Pemasukan</small><b class="green">Rp {{ number_format($income,0,',','.') }}</b></div>
            <div><small>Pengeluaran</small><b>Rp {{ number_format($expense,0,',','.') }}</b></div>
        </div>
        <div class="chart-wrap"><canvas id="cashChart"></canvas></div>
    </section>
    <section class="panel quick-panel">
        <div class="panel-head">
            <div><span class="eyebrow">QUICK ACCESS</span>
                <h3>Yang bisa kamu lakukan</h3>
            </div><span class="spark">✳</span>
        </div><a class="quick-item" href="{{ route('members') }}"><span class="quick-ico mint">♧</span><span><b>Kelola anggota</b><small>Tambah dan perbarui data</small></span><i>↗</i></a><a class="quick-item" href="{{ route('invoices.index') }}"><span class="quick-ico yellow">▤</span><span><b>Generate tagihan</b><small>Terbitkan iuran periode baru</small></span><i>↗</i></a><a class="quick-item" href="{{ route('payments') }}"><span class="quick-ico peach">⇧</span><span><b>Verifikasi pembayaran</b><small>{{ $pending }} bukti menunggu pemeriksaan</small></span><i>↗</i></a><a class="quick-item" href="{{ route('transactions') }}"><span class="quick-ico lilac">⇄</span><span><b>Catat transaksi</b><small>Kelola arus kas organisasi</small></span><i>↗</i></a>
    </section>
</div>
<section class="panel table-panel">
    <div class="panel-head">
        <div><span class="eyebrow">LATEST ACTIVITY</span>
            <h3>Transaksi terbaru</h3>
        </div><a class="text-link" href="{{ route('transactions') }}">Lihat semua →</a>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>TRANSAKSI</th>
                    <th>TANGGAL</th>
                    <th>KATEGORI</th>
                    <th>NOMINAL</th>
                    <th>TIPE</th>
                </tr>
            </thead>
            <tbody>@forelse($recent as $t)<tr>
                    <td><b>{{ $t->description }}</b></td>
                    <td>{{ $t->transaction_date->format('d M Y') }}</td>
                    <td>{{ $t->category }}</td>
                    <td class="amount">Rp {{ number_format($t->amount,0,',','.') }}</td>
                    <td><span class="status {{ $t->type==='pemasukan'?'paid':'rejected' }}">{{ ucfirst($t->type) }}</span></td>
                </tr>@empty<tr>
                    <td colspan="5" class="empty">Belum ada transaksi.</td>
                </tr>@endforelse</tbody>
        </table>
    </div>
</section>@endsection @push('scripts')<script>
    new Chart(document.getElementById('cashChart'), {
        type: 'bar',
        data: {
            labels: ['Pemasukan', 'Pengeluaran'],
            datasets: [{
                data: [{
                    {
                        $income
                    }
                }, {
                    {
                        $expense
                    }
                }],
                backgroundColor: ['#1d7775', '#edb84e'],
                borderRadius: 10,
                barThickness: 58
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#edf0ed'
                    },
                    ticks: {
                        callback: v => 'Rp ' + new Intl.NumberFormat('id-ID', {
                            notation: 'compact'
                        }).format(v)
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
</script>@endpush