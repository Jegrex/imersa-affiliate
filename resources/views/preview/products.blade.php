@extends('layouts.preview')
@section('content')
<x-admin-heading title="Manajemen produk" description="Kelola informasi, status publikasi, dan referensi produk.">
<a class="button" href="{{ route('preview.products.create') }}">
<x-icon name="plus"/>Tambah produk</a>
</x-admin-heading>
<section class="panel">
<form class="admin-filters" method="get">
<label class="field grow">Nama produk<input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama produk..." maxlength="100">
</label>
<label class="field">Kategori<select name="category">
<option value="">Semua kategori</option>
@foreach($categories as $cat)<option value="{{ $cat['slug'] }}" @selected(request('category') === $cat['slug'])>{{ $cat['name'] }}</option>
@endforeach</select>
</label>
<label class="field">Publikasi<select name="status">
<option value="">Semua status</option>
<option value="active" @selected(request('status') === 'active')>Aktif</option>
<option value="draft" @selected(request('status') === 'draft')>Draft</option>
</select>
</label>
<button class="button button-outline" type="submit">Terapkan</button>
<a class="text-link" href="{{ route('preview.products') }}">Reset</a>
@if($empty)<input type="hidden" name="state" value="empty">
@endif</form>
@if($listing->isEmpty())<x-empty title="Tidak ada produk untuk ditampilkan" description="Ubah filter atau mulai dengan menambahkan produk.">
<a class="button" href="{{ route('preview.products.create') }}">Tambah produk</a>
</x-empty>
@else<div class="table-scroll">
<table>
<caption class="sr-only">Daftar produk dan status persetujuan affiliate</caption>
<thead>
<tr>
<th>Produk</th>
<th>Kategori</th>
<th>Publikasi</th>
<th>Affiliate</th>
<th class="numeric">Klik contoh</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>
@foreach($listing as $item)<tr>
<td>
<a class="table-product" href="{{ route('preview.products.edit', $item['id']) }}">
@if($item['image'])<img src="{{ asset('images/demo/'.$item['image']) }}" alt="" width="48" height="44">
@else<span class="table-image-placeholder">
<x-icon name="image"/>
</span>
@endif<span>{{ $item['name'] }}<small>{{ $item['brand'] }}</small>
</span>
</a>
</td>
<td>{{ $item['category']['name'] }}</td>
<td>
<x-status :value="$item['is_active'] ? 'active' : 'draft'"/>
</td>
<td>
<a href="{{ route('preview.products.edit', $item['id']) }}#referensi-shopee" aria-label="Edit URL affiliate {{ $item['name'] }}">
<x-status :value="$item['approval']"/>
</a>
</td>
<td class="numeric">{{ $item['clicks'] }}</td>
<td>
<div class="row-actions">
<a class="icon-button" href="{{ route('preview.products.edit', $item['id']) }}" aria-label="Edit {{ $item['name'] }}">
<x-icon name="edit"/>
</a>
<a class="icon-button" href="{{ route('preview.reviews', $item['id']) }}" aria-label="Referensi review {{ $item['name'] }}">
<x-icon name="review"/>
</a>
<button class="icon-button danger" aria-label="Hapus {{ $item['name'] }}" data-confirm-title="Hapus produk ini?" data-confirm-message="{{ $item['name'] }} akan dinonaktifkan dan disembunyikan. Riwayat tetap dipertahankan. Pada pratinjau ini tidak ada data yang dihapus.">
<x-icon name="trash"/>
</button>
</div>
</td>
</tr>
@endforeach</tbody>
</table>
</div>
@endif<x-pager :listing="$listing"/>
</section>
<p class="small muted below-panel">URL affiliate diisi bersama informasi toko pada bagian Referensi Shopee di form produk.</p>
@endsection
