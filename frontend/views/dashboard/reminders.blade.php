@extends('layouts.app') @section('title','Pengingat') @section('crumb','Pengingat') @section('content')<div class="page-heading">
    <div><span class="eyebrow">PAYMENT REMINDERS</span>
        <h1>Pengingat pembayaran</h1>
        <p>Tagihan belum lunas atau yang mendekati/melewati tenggat.</p>
    </div>
</div>
<section class="panel table-panel">
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
            <tbody>@forelse($items as $i)<tr>
                    <td>{{ $i->member->name }}</td>
                    <td>{{ $i->period_label }}</td>
                    <td>{{ $i->due_date->format('d M Y') }}</td>
                    <td>Rp {{ number_format($i->amount,0,',','.') }}</td>
                    <td><span class="status {{ $i->status }}">{{ str_replace('_',' ',ucfirst($i->status)) }}</span></td>
                </tr>@empty<tr>
                    <td colspan="5" class="empty">Tidak ada tagihan yang perlu diingatkan.</td>
                </tr>@endforelse</tbody>
        </table>
    </div>
</section>@endsection