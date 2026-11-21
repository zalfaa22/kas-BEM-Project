@extends('layouts.app') @section('title','Profil') @section('crumb','Profil') @section('content')<div class="page-heading">
    <div><span class="eyebrow">ACCOUNT SETTINGS</span>
        <h1>Profil saya</h1>
        <p>Perbarui informasi akun yang digunakan untuk mengakses portal.</p>
    </div>
</div>
<section class="panel profile-panel">
    <div class="profile-banner">
        <div class="avatar big">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
        <div>
            <h3>{{ auth()->user()->name }}</h3><span>{{ ucfirst(auth()->user()->role) }} · {{ auth()->user()->nim }}</span>
        </div>
    </div>
    <form method="POST" action="{{ route('profile.update') }}" class="form-stack">@csrf @method('PUT')<label>Nama lengkap</label><input name="name" value="{{ auth()->user()->name }}" required><label>Email</label><input type="email" name="email" value="{{ auth()->user()->email }}" required><label>Password baru <small>(kosongkan jika tidak ingin mengubah)</small></label><input type="password" name="password" minlength="8"><button class="btn primary">Simpan perubahan →</button></form>
</section>@endsection