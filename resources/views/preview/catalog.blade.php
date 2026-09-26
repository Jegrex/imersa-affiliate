@extends('layouts.preview')
@section('content')
<section class="page-intro">
<div class="container">
<span class="eyebrow">PILIHAN ELEKTRONIK</span>
<h1>Temukan yang cocok<br>untuk <span>kebutuhanmu.</span>
</h1>
<p>Cari nama produk atau jelajahi kategori. Mulai dari sini.</p>
</div>
</section>
<section class="container catalog-section">
<form class="catalog-filters" method="get" action="{{ route('preview.catalog') }}">
<div class="search-field">
<x-icon name="search"/>
<label class="sr-only" for="search">Cari nama produk</label>
<input id="search" name="q" type="search" value="{{ request('q') }}" placeholder="Cari nama produk, misalnya MacBook..." maxlength="100">
<button class="button" type="submit">Cari</button>
</div>
<div class="filter-select">
<label for="category">Kategori</label>
<select name="category" id="category">
<option value="">Semua kategori</option>
@foreach($categories->where('active', true) as $cat)<option value="{{ $cat['slug'] }}" @selected(request('category') === $cat['slug'])>{{ $cat['parent_id'] ? '↳ ' : '' }}{{ $cat['name'] }}</option>
@endforeach</select>
</div>
@if($empty)<input type="hidden" name="state" value="empty">
@endif</form>
<div class="results-heading">
<span>
<strong>{{ $listing->total() }}</strong> produk ditemukan</span>
@if(request('q') || request('category') || $empty)<a class="text-link" href="{{ route('preview.catalog') }}">Reset pencarian<x-icon name="close"/>
</a>
@else<span class="muted small">Temukan detail sebelum memilih</span>
@endif</div>
@if($listing->isEmpty())<x-empty title="Belum ada produk yang cocok" description="Coba nama lain atau hapus filter untuk melihat pilihan yang tersedia.">
<a class="button button-outline" href="{{ route('preview.catalog') }}">Lihat semua produk</a>
</x-empty>
@else<div class="product-grid">
@foreach($listing as $item)<x-product-card :product="$item"/>
@endforeach</div>
@endif
<x-pager :listing="$listing"/>
</section>
@endsection
