@extends('layouts.preview')
@section('content')
<div class="container detail-container">
<nav class="breadcrumb" aria-label="Breadcrumb">
<a href="{{ route('preview.home') }}">Beranda</a>
<span>/</span>
<a href="{{ route('preview.catalog') }}">Katalog</a>
<span>/</span>
<span>{{ $product['name'] }}</span>
</nav>
<div class="detail-grid">
<div>
<div class="detail-image">
@if($product['image'])<img id="main-product-image" src="{{ asset('images/demo/'.$product['image']) }}" alt="{{ $product['name'] }} — gambar contoh" width="700" height="560" data-media>
@endif<span class="media-fallback" @if($product['image']) hidden @endif>
<x-icon name="image"/>Gambar belum tersedia</span>
</div>
<p class="image-caption">Gambar dan informasi pada pratinjau ini menggunakan data contoh.</p>
</div>
<div class="detail-info">
<div class="inline-actions">
<span class="eyebrow">{{ $product['brand'] }}</span>
<span class="badge badge-neutral">{{ $product['category']['name'] }}</span>
</div>
<h1>{{ $product['name'] }}</h1>
<div class="detail-rating">
@if($product['shopee_rating'] !== null)<span class="rating">
<x-icon name="star"/>{{ $product['shopee_rating'] }}</span>
<span>Rating Shopee</span>
@else<span class="muted">Rating belum tersedia</span>
@endif @if($product['shopee_review_count'] !== null)<span class="muted">· {{ $product['shopee_review_count'] }} ulasan</span>
@endif</div>
<p class="detail-price">{{ $product['price_amount'] === null ? 'Harga tidak tersedia' : 'Rp '.number_format($product['price_amount'], 0, ',', '.') }}</p>
<p class="small muted">Periksa harga dan ketersediaan terbaru di marketplace.</p>
<div class="spec-summary">
<h2>Informasi utama</h2>
<dl>
@foreach($product['specifications'] as $spec)<div>
<dt>{{ $spec['name'] }}</dt>
<dd>{{ $spec['value'] }}</dd>
</div>
@endforeach</dl>
</div>
@if($product['approval'] === 'approved')<button class="button full buy-button" data-feedback-title="Pratinjau tombol Shopee" data-feedback-message="Pada aplikasi yang terhubung, server memeriksa persetujuan tautan dan menyimpan klik sebelum membuka Shopee. Pratinjau ini tidak mencatat klik atau membuka tautan pembelian.">Lanjut ke Shopee<x-icon name="arrow"/>
</button>
<p class="affiliate-note">
<x-icon name="link"/>Tautan affiliate. Pembelian dan pembayaran dilakukan di Shopee.</p>
@else<button class="button full" disabled>Tautan pembelian belum tersedia</button>
<p class="affiliate-note">Kamu tetap dapat membaca informasi produk. Tautan akan tersedia setelah proses persetujuan selesai.</p>
@endif
</div>
</div>
<div class="detail-lower">
<section class="panel prose">
<span class="eyebrow">LEBIH DEKAT</span>
<h2>Tentang produk</h2>
<p>{{ $product['description'] }}</p>
</section>
<section class="panel">
<div class="section-heading">
<div>
<h2>Referensi ulasan</h2>
<p class="small muted">Referensi Shopee yang ditampilkan tidak menentukan rating agregat produk.</p>
</div>
</div>
@forelse($reviews->where('product_id', $product['id'])->where('visible', true) as $review)<article class="review-card">
<div class="review-header">
<span class="review-avatar">{{ mb_substr($review['name'], 0, 1) }}</span>
<div>
<strong>{{ $review['name'] }}</strong>
<p class="small muted">{{ $review['source'] }}@if($review['date']) · {{ $review['date'] }}@endif</p>
</div>
@if($review['rating'] !== null)<span class="rating">
<x-icon name="star"/>{{ $review['rating'] }}/5</span>
@endif</div>
<p>{{ $review['content'] }}</p>
</article>
@empty<p class="muted">Referensi ulasan belum tersedia untuk produk ini.</p>
@endforelse</section>
</div>
<div class="back-to-catalog">
<a class="text-link" href="{{ route('preview.catalog') }}">
<x-icon name="back"/>Jelajahi produk lainnya</a>
</div>
</div>
@endsection
