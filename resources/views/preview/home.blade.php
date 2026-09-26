@extends('layouts.preview')
@section('content')
<section class="hero">
<div class="container hero-grid">
<div class="hero-copy">
<span class="hero-kicker">
<span>
</span>KATALOG ELEKTRONIK IMERSA</span>
<h1>Teknologi pilihan.<br>Untuk setiap<br>
<em>keseharian.</em>
</h1>
<p>Mulai dari meja kerja sampai perjalananmu. Temukan produk elektronik dan informasi yang membantumu memilih.</p>
<a class="button button-light" href="{{ route('preview.catalog') }}">Jelajahi katalog<x-icon name="arrow"/>
</a>
<div class="hero-note">
<x-icon name="shield"/>Informasi produk · Referensi Shopee · Tautan affiliate</div>
</div>
<div class="hero-art">
<span class="hero-orbit">
</span>
<div class="hero-product">
<span class="eyebrow">MEET YOUR EVERYDAY COMPANION</span>
<img src="{{ asset('images/demo/macbook.webp') }}" alt="Ilustrasi laptop dari referensi desain" width="640" height="480" fetchpriority="high">
<div>
<span>
<small>Pilihan untuk produktivitas</small>
<strong>Ringkas. Siap menemani.</strong>
</span>
<a href="{{ route('preview.detail', 'macbook-air-m2') }}" aria-label="Lihat MacBook Air M2">
<x-icon name="arrow"/>
</a>
</div>
</div>
<span class="hero-caption">01 / WORK & EVERYDAY</span>
</div>
</div>
</section>
<section class="section container">
<div class="section-heading">
<div>
<span class="eyebrow">MULAI DARI KEBUTUHANMU</span>
<h2>Temukan kategorimu.</h2>
</div>
<a class="text-link" href="{{ route('preview.catalog') }}">Semua produk<x-icon name="arrow"/>
</a>
</div>
<div class="category-grid">
@foreach($categories->whereNull('parent_id')->where('active', true) as $cat)<a class="category-card" href="{{ route('preview.catalog', ['category' => $cat['slug']]) }}">
<span class="category-icon">
<x-icon :name="$cat['icon']"/>
</span>
<h3>{{ $cat['name'] }}</h3>
<p>{{ $cat['description'] }}</p>
<x-icon name="arrow" class="category-arrow"/>
</a>
@endforeach</div>
</section>
<section class="section section-soft">
<div class="container">
<div class="section-heading">
<div>
<span class="eyebrow">KENALI LEBIH DEKAT</span>
<h2>Pilihan untuk dijelajahi.</h2>
<p>Informasi produk dalam satu tempat, keputusan tetap di tanganmu.</p>
</div>
<a class="text-link" href="{{ route('preview.catalog') }}">Buka katalog<x-icon name="arrow"/>
</a>
</div>
@if($publicProducts->isEmpty())<x-empty title="Pilihan produk segera hadir" description="Katalog akan diperbarui setelah produk tersedia."/>
@else<div class="product-grid">
@foreach($publicProducts->take(4) as $item)<x-product-card :product="$item"/>
@endforeach</div>
@endif</div>
</section>
<section class="section container">
<div class="section-heading centered">
<div>
<span class="eyebrow">SEBELUM KAMU MEMILIH</span>
<h2>Lebih jelas, lebih nyaman.</h2>
</div>
</div>
<div class="benefit-grid">
<article>
<span class="category-icon">
<x-icon name="search"/>
</span>
<h3>Kenali produknya</h3>
<p>Baca spesifikasi dan informasi yang tersedia untuk menyesuaikan dengan kebutuhanmu.</p>
</article>
<article>
<span class="category-icon">
<x-icon name="review"/>
</span>
<h3>Lihat referensinya</h3>
<p>Referensi ulasan ditampilkan bila tersedia. Data yang belum ada tidak kami isi dengan perkiraan.</p>
</article>
<article>
<span class="category-icon">
<x-icon name="link"/>
</span>
<h3>Lanjutkan di Shopee</h3>
<p>Tautan affiliate mengantarmu ke marketplace. Pembelian dan pembayaran berlangsung di sana.</p>
</article>
</div>
</section>
@endsection
