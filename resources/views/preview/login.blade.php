@extends('layouts.preview')
@section('content')
<div class="login-layout">
<div class="login-story">
<a class="brand" href="{{ route('preview.home') }}">
<span class="brand-mark">i</span>Imersa</a>
<span class="hero-kicker">PORTAL PENGELOLAAN</span>
<h1>Informasi yang baik<br>dimulai dari<br>
<em>pengelolaan yang baik.</em>
</h1>
<p>Kelola katalog elektronik dan referensi produk dalam satu ruang kerja.</p>
<span class="small">PT Imersa Solusi Teknologi</span>
</div>
<div class="login-main">
<a class="text-link login-back" href="{{ route('preview.home') }}">
<x-icon name="back"/>Kembali ke website</a>
<div class="login-card">
<span class="empty-symbol">
<x-icon name="shield"/>
</span>
<h2>Selamat datang kembali.</h2>
<p class="muted">Masuk ke ruang kerja Admin Imersa.</p>
<div class="notice">
<x-icon name="info"/>
<p>Ini pratinjau formulir. Autentikasi belum terhubung. Jangan masukkan kredensial asli.</p>
</div>
<form method="post" action="{{ url()->current() }}" data-preview-form data-success-message="Tampilan form valid. Login belum terhubung ke backend. Gunakan tombol Buka dashboard contoh untuk meninjau halaman Admin.">
@csrf<x-form-alerts/>
<label class="field">Email Admin<input name="email" type="email" placeholder="admin@example.test" autocomplete="off" required>
</label>
<label class="field">Password<input type="password" placeholder="Belum digunakan dalam pratinjau" disabled aria-describedby="password-help">
</label>
<p id="password-help" class="field-help">Input password diaktifkan saat autentikasi backend tersedia.</p>
<button class="button full" type="submit">Periksa form login<x-icon name="arrow"/>
</button>
</form>
<a class="button button-outline full" href="{{ route('preview.dashboard') }}">Buka dashboard contoh</a>
<p class="login-help">Akses akun dikelola oleh administrator PT Imersa.</p>
</div>
</div>
</div>
@endsection
