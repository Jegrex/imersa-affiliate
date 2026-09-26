@extends('layouts.preview')
@section('content')
<x-admin-heading title="Semua dalam pandangan." description="Ringkasan katalog dan aktivitas klik affiliate.">
<a class="button" href="{{ route('preview.products.create') }}">
<x-icon name="plus"/>Tambah produk</a>
</x-admin-heading>
<div class="dashboard-welcome">
<div>
<span class="eyebrow">RUANG KERJA KATALOG</span>
<h2>Siapkan pilihan berikutnya.</h2>
<p>{{ $products->where('is_active', false)->count() }} produk contoh masih dalam draft. Lengkapi informasinya sebelum diterbitkan.</p>
<a class="text-link" href="{{ route('preview.products', ['status' => 'draft']) }}">Tinjau draft<x-icon name="arrow"/>
</a>
</div>
<x-icon name="box"/>
</div>
<div class="metric-grid">
<x-metric label="Produk aktif" :value="$empty ? 0 : $products->where('is_active', true)->count()" icon="box" note="Terlihat di katalog publik"/>
<x-metric label="Produk draft" :value="$empty ? 0 : $products->where('is_active', false)->count()" icon="edit" note="Belum diterbitkan"/>
<x-metric label="Kategori aktif" :value="$empty ? 0 : $categories->where('active', true)->count()" icon="folder" note="Termasuk subkategori"/>
<x-metric label="Klik dalam periode" :value="number_format($totalClicks, 0, ',', '.')" icon="link" note="1–24 September 2026 · data contoh"/>
</div>
<div class="dashboard-columns">
<section class="panel">
<div class="section-heading">
<div>
<h2>Paling sering dijelajahi</h2>
<p class="small muted">Peringkat berdasarkan klik, bukan pembelian.</p>
</div>
<a class="text-link" href="{{ route('preview.analytics') }}">Lihat analitik<x-icon name="arrow"/>
</a>
</div>
@if($totalClicks === 0)<x-empty title="Belum ada aktivitas klik" description="Ringkasan akan muncul setelah data klik tersedia."/>
@else<div class="ranking-list">
@foreach($ranking->take(5) as $item)<div>
<span class="rank-index">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
<img src="{{ asset('images/demo/'.$item['image']) }}" alt="" width="54" height="48">
<span class="rank-name">
<strong>{{ $item['name'] }}</strong>
<small>{{ $item['category']['name'] }}</small>
</span>
<strong>{{ $item['period_clicks'] }}<small>klik</small>
</strong>
</div>
@endforeach</div>
@endif</section>
<section class="panel checklist">
<span class="category-icon">
<x-icon name="shield"/>
</span>
<h2>Sebelum dipublikasikan</h2>
<p class="muted">Pastikan informasi produk siap dibaca pengunjung.</p>
<ul>
<li>
<x-icon name="check"/>Kategori aktif dan sesuai</li>
<li>
<x-icon name="check"/>Informasi serta sumber jelas</li>
<li>
<x-icon name="check"/>Konfirmasi lingkup elektronik</li>
<li>
<x-icon name="check"/>Persetujuan tautan terpisah</li>
</ul>
<a class="button button-outline full" href="{{ route('preview.products') }}">Kelola produk<x-icon name="arrow"/>
</a>
</section>
</div>
@endsection
