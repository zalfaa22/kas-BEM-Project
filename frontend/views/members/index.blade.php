
@extends('layouts.app')

@section('title', 'Data Anggota')
@section('crumb', 'Data Anggota')

@section('content')

<div class="page-heading">
    <div>
        <span class="eyebrow">MEMBERS DIRECTORY</span>
        <h1>Data anggota</h1>
        <p>Kelola identitas dan status keanggotaan BEM FILKOM UB.</p>
    </div>

    <button class="btn primary" onclick="openModal('memberModal')">
        ＋ Tambah anggota
    </button>
</div>

<section class="panel table-panel">

    <form class="toolbar">
        <input
            name="q"
            value="{{ request('q') }}"
            placeholder="Cari nama atau NIM..."
        >

        <select name="status">
            <option value="">Semua status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>
                Aktif
            </option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>
                Nonaktif
            </option>
        </select>

        <button class="btn light">Filter</button>
    </form>

    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>ANGGOTA</th>
                    <th>NIM</th>
                    <th>DEPARTEMEN</th>
                    <th>EMAIL</th>
                    <th>STATUS</th>
                    <th>ROLE</th>
                </tr>
            </thead>

            <tbody>
                @foreach($members as $m)
                    <tr>
                        <td>
                            <div class="person">
                                <span class="avatar">
                                    {{ strtoupper(substr($m->name, 0, 1)) }}
                                </span>
                                <b>{{ $m->name }}</b>
                            </div>
                        </td>

                        <td>{{ $m->nim }}</td>
                        <td>{{ $m->department?->name ?? '—' }}</td>
                        <td>{{ $m->email }}</td>

                        <td>
                            <span class="status {{ $m->status_anggota === 'aktif' ? 'paid' : 'rejected' }}">
                                {{ ucfirst($m->status_anggota) }}
                            </span>
                        </td>

                        <td>{{ ucfirst($m->role) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $members->links() }}
    </div>

</section>

<div class="modal" id="memberModal">
    <div class="modal-card">

        <div class="modal-head">
            <div>
                <span class="eyebrow">NEW MEMBER</span>
                <h3>Tambah anggota</h3>
            </div>

            <button class="close" onclick="closeModal('memberModal')">
                ×
            </button>
        </div>

        <form method="POST" action="{{ route('members.store') }}" class="form-grid">
            @csrf

            <label>
                Nama lengkap
                <input name="name" required>
            </label>

            <label>
                NIM
                <input name="nim">
            </label>

            <label>
                Email
                <input type="email" name="email" required>
            </label>

            <label>
                Password awal
                <input type="password" name="password" minlength="8" required>
            </label>

            <label>
                Departemen
                <select name="department_id">
                    <option value="">Pilih departemen</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}">
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Status
                <select name="status_anggota">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </label>

            <button class="btn primary full span-2">
                Simpan anggota
            </button>
        </form>

    </div>
</div>

@endsection
