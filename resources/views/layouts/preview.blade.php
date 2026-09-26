@php
    $admin = !in_array($page, ['home', 'catalog', 'detail', 'login']);
    $titles = ['home' => 'Elektronik untuk keseharianmu', 'catalog' => 'Katalog produk', 'detail' => $product['name'] ?? 'Produk', 'login' => 'Portal Admin', 'dashboard' => 'Dashboard', 'products' => 'Manajemen produk', 'product-form' => $product ? 'Edit produk' : 'Tambah produk', 'categories' => 'Manajemen kategori', 'category-form' => $category ? 'Edit kategori' : 'Tambah kategori', 'reviews' => 'Referensi review', 'analytics' => 'Analitik klik', 'imports' => 'Review impor'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>{{ $titles[$page] }} · Imersa</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="{{ $admin ? 'admin-body' : ($page === 'login' ? 'login-body' : 'public-body') }}">
<a class="skip-link" href="#main">Lewati ke konten</a>
<div class="preview-bar">
<div>
<span class="preview-dot">
</span>
<strong>Pratinjau frontend</strong>
<span class="preview-explanation">Data contoh · belum terhubung ke backend</span>
</div>
<div class="inline-actions">
<a href="{{ $admin ? route('preview.home') : route('preview.dashboard') }}">{{ $admin ? 'Lihat website' : 'Lihat Admin' }} <span aria-hidden="true">↗</span>
</a>
@if(in_array($page, ['home','catalog','dashboard','products','categories','analytics','reviews']))<a href="{{ request()->fullUrlWithQuery(['state' => $empty ? null : 'empty']) }}">{{ $empty ? 'Data contoh' : 'Uji data kosong' }}</a>
@endif</div>
</div>
@if($admin)
    <header class="admin-mobile">
<a class="brand" href="{{ route('preview.dashboard') }}">
<span class="brand-mark">i</span>Imersa<span class="brand-suffix">/ admin</span>
</a>
<button class="icon-button" data-menu="admin-nav" aria-expanded="false" aria-controls="admin-nav" aria-label="Buka navigasi Admin">
<x-icon name="menu"/>
</button>
</header>
    <aside class="sidebar" id="admin-nav">
<a class="brand" href="{{ route('preview.dashboard') }}">
<span class="brand-mark">i</span>Imersa<span class="brand-suffix">/ admin</span>
</a>
<p class="nav-heading">RUANG KELOLA</p>
<nav aria-label="Navigasi Admin">
    @foreach(['dashboard' => ['grid','Dashboard'], 'products' => ['box','Produk'], 'categories' => ['folder','Kategori'], 'analytics' => ['chart','Analitik klik'], 'imports' => ['folder','Review impor']] as $route => [$icon,$label])
        @php($selected = $page === $route || ($route === 'products' && in_array($page, ['product-form','reviews'])) || ($route === 'categories' && $page === 'category-form'))
        <a href="{{ route('preview.'.$route) }}" @if($selected) aria-current="page" @endif>
<x-icon :name="$icon"/>{{ $label }}@if($route === 'imports')<span class="nav-label">Ditunda</span>
@endif</a>
    @endforeach
    </nav>
<div class="sidebar-bottom">
<p>Workspace contoh</p>
<span class="small">PT Imersa Solusi Teknologi</span>
<a href="{{ route('preview.login') }}">
<x-icon name="logout"/>Keluar dari pratinjau</a>
</div>
</aside>
    <div class="admin-shell">
<header class="admin-topbar">
<span>Workspace <span class="muted">/ {{ $titles[$page] }}</span>
</span>
<span class="admin-avatar" aria-label="Admin contoh">AD</span>
</header>
<main id="main" class="admin-content" tabindex="-1">
@yield('content')</main>
<footer class="admin-footer">PT Imersa Solusi Teknologi <span>Frontend preview · September 2026</span>
</footer>
</div>
@else
    @if($page !== 'login')
    <header class="site-header">
<div class="container header-inner">
<a class="brand" href="{{ route('preview.home') }}">
<span class="brand-mark">i</span>Imersa<span class="brand-suffix">solusi teknologi</span>
</a>
<button class="icon-button mobile-menu" data-menu="public-nav" aria-controls="public-nav" aria-expanded="false" aria-label="Buka navigasi">
<x-icon name="menu"/>
</button>
<nav id="public-nav" aria-label="Navigasi utama">
<a href="{{ route('preview.home') }}" @if($page === 'home') aria-current="page" @endif>Beranda</a>
<a href="{{ route('preview.catalog') }}" @if(in_array($page, ['catalog','detail'])) aria-current="page" @endif>Katalog produk</a>
<a class="admin-entry" href="{{ route('preview.login') }}">Portal Admin<x-icon name="arrow"/>
</a>
</nav>
</div>
</header>
    @endif
    <main id="main" tabindex="-1">
@yield('content')</main>
    @if($page !== 'login')<footer class="site-footer">
<div class="container">
<div class="footer-grid">
<div>
<a class="brand" href="{{ route('preview.home') }}">
<span class="brand-mark">i</span>Imersa</a>
<p>Temukan elektronik untuk kebutuhanmu.<br>Jelajahi informasi, lanjutkan pembelian di Shopee.</p>
</div>
<div>
<h2>Jelajahi</h2>
<a href="{{ route('preview.catalog') }}">Semua produk</a>
@foreach($categories->whereNull('parent_id')->where('active', true)->take(3) as $cat)<a href="{{ route('preview.catalog', ['category' => $cat['slug']]) }}">{{ $cat['name'] }}</a>
@endforeach</div>
<div>
<h2>Tentang katalog</h2>
<p>Website ini menggunakan tautan affiliate.<br>Transaksi dilakukan di marketplace tujuan.</p>
<span class="small">Identitas dan channel resmi menunggu konfirmasi PT Imersa.</span>
</div>
</div>
<div class="footer-bottom">
<span>© 2026 PT Imersa Solusi Teknologi</span>
<span>Informasi yang jelas. Pilihan di tanganmu.</span>
</div>
</div>
</footer>
@endif
@endif
<dialog id="feedback-dialog" aria-labelledby="feedback-title">
<div class="dialog-content">
<button class="icon-button dialog-close" data-close aria-label="Tutup dialog">
<x-icon name="close"/>
</button>
<span class="empty-symbol">
<x-icon name="info"/>
</span>
<h2 id="feedback-title">Pratinjau interaksi</h2>
<p id="feedback-message">
</p>
<div class="inline-actions dialog-actions">
<button class="button button-outline" data-close>Tutup</button>
<button class="button" id="feedback-confirm" hidden>Konfirmasi</button>
</div>
</div>
</dialog>
<div id="toast" class="toast" role="status" hidden>
</div>
</body>
</html>
