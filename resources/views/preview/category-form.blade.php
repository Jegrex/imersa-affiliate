@extends('layouts.preview')
@section('content')
<a class="text-link back-link" href="{{ route('preview.categories') }}">
<x-icon name="back"/>Kembali ke kategori</a>
<x-admin-heading :title="$category ? 'Edit kategori' : 'Tambah kategori'" description="Kategori bertingkat membantu pengunjung mempersempit pilihan."/>
<form class="form-narrow" method="post" action="{{ url()->current() }}" data-preview-form data-category-form data-active-products="{{ $category ? $products->where('category_id', $category['id'])->where('is_active', true)->count() : 0 }}">
@csrf<x-form-alerts/>
<section class="panel">
<div class="form-grid">
<label class="field">Nama kategori <span class="required">*</span>
<input name="name" required maxlength="255" value="{{ $category['name'] ?? '' }}" placeholder="Contoh: Laptop kerja" data-slug-source>
</label>
<label class="field">Slug <span class="required">*</span>
<input name="slug" required maxlength="255" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" title="Gunakan huruf kecil, angka, dan tanda hubung" value="{{ $category['slug'] ?? '' }}" placeholder="laptop-kerja" data-slug-target>
</label>
<label class="field">Kategori induk<select name="parent_id">
<option value="">Tanpa induk (kategori utama)</option>
@foreach($categories as $cat)@if($cat['id'] !== ($category['id'] ?? null) && $cat['parent_id'] !== ($category['id'] ?? -1))<option value="{{ $cat['id'] }}" @selected(($category['parent_id'] ?? null) === $cat['id'])>{{ $cat['name'] }}</option>
@endif
@endforeach</select>
</label>
<label class="field">Urutan tampilan<input type="number" name="sort_order" min="0" value="0" required>
</label>
<label class="field span-2">Deskripsi<textarea name="description" rows="4">{{ $category['description'] ?? '' }}</textarea>
</label>
<label class="field span-2">Status<select name="is_active">
<option value="1" @selected($category['active'] ?? true)>Aktif</option>
<option value="0" @selected(!($category['active'] ?? true))>Nonaktif</option>
</select>
</label>
</div>
<div class="notice">
<x-icon name="info"/>
<p>Kategori yang masih dipakai produk aktif tidak dapat dinonaktifkan. Seluruh perubahan form ditolak jika pemeriksaan gagal.</p>
</div>
<div class="form-actions">
<a class="button button-outline" href="{{ route('preview.categories') }}">Batal</a>
<button class="button" type="submit">Periksa & simulasikan simpan</button>
</div>
</section>
</form>
@endsection
