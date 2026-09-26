@extends('layouts.preview')
@section('content')
<x-admin-heading title="Manajemen kategori" description="Susun kategori dan subkategori agar produk mudah ditemukan.">
<a class="button" href="{{ route('preview.categories.create') }}">
<x-icon name="plus"/>Tambah kategori</a>
</x-admin-heading>
<section class="panel">
@if($empty)<x-empty title="Kategori belum tersedia" description="Mulai dengan membuat kategori untuk katalog.">
<a class="button" href="{{ route('preview.categories.create') }}">Tambah kategori</a>
</x-empty>
@else<div class="table-scroll">
<table>
<caption class="sr-only">Daftar kategori dan hubungan induk</caption>
<thead>
<tr>
<th>Kategori</th>
<th>Slug</th>
<th>Kategori induk</th>
<th>Status</th>
<th class="numeric">Produk</th>
<th>Aksi</th>
</tr>
</thead>
<tbody>
@foreach($categories->sortBy(fn($cat) => ($cat['parent_id'] ?? $cat['id']).'-'.($cat['parent_id'] ? '1' : '0')) as $cat)<tr>
<td>
<a class="category-name {{ $cat['parent_id'] ? 'is-child' : '' }}" href="{{ route('preview.categories.edit', $cat['id']) }}">
<x-icon :name="$cat['icon']"/>{{ $cat['name'] }}</a>
</td>
<td>
<span class="muted small">{{ $cat['slug'] }}</span>
</td>
<td>{{ $categories->firstWhere('id', $cat['parent_id'])['name'] ?? '—' }}</td>
<td>
<x-status :value="$cat['active'] ? 'active' : 'draft'"/>
</td>
<td class="numeric">{{ $products->where('category_id', $cat['id'])->count() }}</td>
<td>
<div class="row-actions">
<a class="icon-button" href="{{ route('preview.categories.edit', $cat['id']) }}" aria-label="Edit {{ $cat['name'] }}">
<x-icon name="edit"/>
</a>
@if($products->where('category_id', $cat['id'])->isNotEmpty() || $categories->where('parent_id', $cat['id'])->isNotEmpty())<button class="icon-button danger" aria-label="Periksa penghapusan {{ $cat['name'] }}" data-feedback-title="Kategori masih digunakan" data-feedback-message="Kategori ini masih terhubung dengan produk atau subkategori. Atur referensinya terlebih dahulu sebelum menghapus kategori.">
<x-icon name="trash"/>
</button>
@else<button class="icon-button danger" aria-label="Hapus {{ $cat['name'] }}" data-confirm-title="Hapus kategori?" data-confirm-message="Kategori akan disembunyikan setelah backend memeriksa seluruh referensi. Pratinjau tidak menghapus data.">
<x-icon name="trash"/>
</button>
@endif</div>
</td>
</tr>
@endforeach</tbody>
</table>
</div>
@endif</section>
<p class="small muted below-panel">Kategori nonaktif hanya dapat dipilih untuk produk draft.</p>
@endsection
