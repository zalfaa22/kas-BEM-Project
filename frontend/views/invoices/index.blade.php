@extends('layouts.app') @section('title','Tagihan') @section('crumb','Tagihan') @section('content')<div class="page-heading">
    <div><span class="eyebrow">INVOICE MANAGEMENT</span>
        <h1>Tagihan iuran</h1>
        <p>Generate tagihan untuk seluruh anggota aktif dalam satu proses.</p>
    </div>
</div>
<section class="panel generate-panel">
    <div><span class="eyebrow">BULK GENERATION</span>
        <h3>Terbitkan tagihan baru</h3>
        <p>Sistem akan mengambil anggota aktif dan mencegah tagihan ganda pada periode yang sama.</p>
    </div>
    <form method="POST" action="{{ route('invoices.generate') }}" class="generate-form">@csrf<label>Aturan iuran<select name="dues_rule_id" required>@foreach(\App\Models\DuesRule::where('active',true)->get() as $r)<option value="{{ $r->id }}">{{ $r->name }} — Rp {{ number_format($r->amount,0,',','.') }}</option>@endforeach</select></label><label>Label periode<input name="period_label" placeholder="Desember 2026" required></label><label>Tanggal jatuh tempo<input type="date" name="due_date" required></label><button class="btn primary">Generate tagihan ↗</button></form>
</section>
<section class="panel table-panel">
    <div class="panel-head">
        <div><span class="eyebrow">ALL INVOICES</span>
            <h3>Daftar tagihan anggota</h3>
        </div>
        <form><select name="status" onchange="this.form.submit()">
                <option value="">Semua status</option>@foreach(['belum_bayar','menunggu_verifikasi','lunas','ditolak'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
            </select></form>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>ANGGOTA</th>
                    <th>PERIODE</th>
                    <th>JATUH TEMPO</th>
                    <th>NOMINAL</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>@foreach($invoices as $i)<tr>
                    <td><b>{{ $i->member->name }}</b></td>
                    <td>{{ $i->period_label }}</td>
                    <td>{{ $i->due_date->format('d M Y') }}</td>
                    <td>Rp {{ number_format($i->amount,0,',','.') }}</td>
                    <td><span class="status {{ $i->status }}">{{ str_replace('_',' ',ucfirst($i->status)) }}</span></td>
                </tr>@endforeach</tbody>
        </table>
    </div>
    <div class="pagination">{{ $invoices->links() }}</div>
</section>@endsection