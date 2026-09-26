@extends('layouts.preview')
@section('content')
<a class="text-link back-link" href="{{ route('preview.products') }}">
<x-icon name="back"/>Kembali ke produk</a>
<x-admin-heading :title="$product ? 'Edit produk' : 'Tambah produk'" description="Lengkapi informasi yang tersedia. Data opsional boleh dikosongkan."/>
<form method="post" action="{{ url()->current() }}" data-preview-form data-product-form>
@csrf<x-form-alerts/>
<div class="form-layout">
<div class="form-sections">
<section class="panel">
<div class="form-section-heading">
<span>01</span>
<div>
<h2>Informasi dasar</h2>
<p>Nama dan kategori membantu pengunjung menemukan produk.</p>
</div>
</div>
<div class="form-grid">
<label class="field span-2">Nama produk <span class="required">*</span>
<input name="name" required maxlength="255" value="{{ $product['name'] ?? '' }}" placeholder="Contoh: MacBook Air M2" data-slug-source>
</label>
<label class="field">Slug <span class="required">*</span>
<input name="slug" required maxlength="255" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" title="Gunakan huruf kecil, angka, dan tanda hubung" value="{{ $product['slug'] ?? '' }}" placeholder="macbook-air-m2" data-slug-target>
</label>
<label class="field">Kategori<select name="category_id" data-product-category>
<option value="">Belum dipilih (draft)</option>
@foreach($categories as $cat)<option value="{{ $cat['id'] }}" data-active="{{ $cat['active'] ? '1' : '0' }}" @selected(($product['category_id'] ?? null) === $cat['id'])>{{ $cat['parent_id'] ? '↳ ' : '' }}{{ $cat['name'] }}{{ !$cat['active'] ? ' — hanya draft' : '' }}</option>
@endforeach</select>
</label>
<label class="field span-2">Label <span class="optional">opsional</span>
<input name="label" maxlength="100" value="{{ $product['label'] ?? '' }}" placeholder="Contoh: Ringkas untuk bekerja">
</label>
<label class="field">Harga <span class="optional">opsional</span>
<input name="price_amount" type="number" min="0" max="9999999999999.99" step="0.01" value="{{ $product['price_amount'] ?? '' }}" placeholder="Kosongkan jika belum tersedia">
</label>
<label class="field">Mata uang<select name="price_currency">
<option value="">Pilih jika harga tersedia</option>
<option value="IDR" @selected(($product['price_currency'] ?? null) === 'IDR')>IDR — Rupiah (contoh)</option>
</select>
</label>
</div>
<p class="field-help">Format mata uang contoh menggunakan IDR; pilihan production menunggu konfirmasi tim.</p>
</section>
<section class="panel">
<div class="form-section-heading">
<span>02</span>
<div>
<h2>Deskripsi & spesifikasi</h2>
<p>Informasi produk yang jelas dan sesuai sumbernya.</p>
</div>
</div>
<label class="field">Deskripsi<textarea name="description_html" rows="5" placeholder="Tuliskan deskripsi produk...">{{ $product['description'] ?? '' }}</textarea>
</label>
<p class="field-help">Input ditampilkan sebagai teks pada preview. Pemeriksaan HTML dilakukan backend saat integrasi.</p>
<div class="subheading">
<h3>Spesifikasi</h3>
<button class="text-link" type="button" data-add-row="specifications">
<x-icon name="plus"/>Tambah spesifikasi</button>
</div>
<div data-collection="specifications">
@foreach($product['specifications'] ?? [['name' => '', 'value' => '']] as $spec)<div class="repeat-row">
<label class="field">Nama<input name="specifications[{{ $loop->index }}][name]" value="{{ $spec['name'] }}" maxlength="255" placeholder="Contoh: Model">
</label>
<label class="field grow">Nilai<input name="specifications[{{ $loop->index }}][value]" value="{{ $spec['value'] }}" placeholder="Nilai spesifikasi">
</label>
<button type="button" class="icon-button danger" data-remove-row aria-label="Hapus baris spesifikasi">
<x-icon name="trash"/>
</button>
</div>
@endforeach</div>
</section>
<section class="panel">
<div class="form-section-heading">
<span>03</span>
<div>
<h2>Gambar & video</h2>
<p>Gunakan URL media dari penyedia yang disetujui.</p>
</div>
</div>
<div class="notice">
<x-icon name="info"/>
<p>Gambar menggunakan URL eksternal. Upload file tidak termasuk dalam fitur katalog.</p>
</div>
<div data-collection="images">
<div class="repeat-row image-row">
<label class="field grow">URL gambar<input type="url" name="images[0][image_url]" placeholder="https://penyedia-disetujui/produk.jpg" data-https>
</label>
<label class="field">Teks alternatif<input name="images[0][alt_text]" maxlength="255" placeholder="Deskripsi singkat gambar">
</label>
<label class="field order-field">Urutan<input type="number" name="images[0][sort_order]" min="0" value="0">
</label>
<button type="button" class="icon-button danger" data-remove-row aria-label="Hapus baris gambar">
<x-icon name="trash"/>
</button>
</div>
</div>
<button class="text-link add-row-button" type="button" data-add-row="images">
<x-icon name="plus"/>Tambah URL gambar</button>
<label class="field">URL video <span class="optional">opsional</span>
<input name="review_video_url" type="url" placeholder="https://penyedia-disetujui/video" data-https>
</label>
<p class="field-help">URL yang diisi tidak dimuat oleh preview. Daftar penyedia dan pemeriksaan keamanan menjadi bagian integrasi backend.</p>
</section>
<section class="panel" id="referensi-shopee">
<div class="form-section-heading">
<span>04</span>
<div>
<h2>Referensi Shopee</h2>
<p>Isi hanya informasi dari sumber yang tersedia.</p>
</div>
</div>
<div class="form-grid">
<label class="field">Nama toko<input name="store_name" maxlength="255" value="{{ $product['store_name'] ?? '' }}" placeholder="Nama toko sumber">
</label>
<label class="field">URL toko<input name="store_url" type="url" data-https value="{{ $product['store_url'] ?? '' }}" placeholder="https://...">
</label>
<label class="field">Rating Shopee<input name="shopee_rating" type="number" min="0" max="5" step="0.01" value="{{ $product['shopee_rating'] ?? '' }}" placeholder="0–5; boleh kosong">
</label>
<label class="field">Jumlah ulasan Shopee<input name="shopee_review_count" type="number" min="0" step="1" value="{{ $product['shopee_review_count'] ?? '' }}" placeholder="Kosongkan jika tidak tersedia">
</label>
<label class="field span-2">URL affiliate<input name="affiliate_url" type="url" data-https maxlength="2048" value="{{ $product['affiliate_url'] ?? '' }}" placeholder="https://..." aria-describedby="affiliate-url-help">
</label>
</div>
<p id="affiliate-url-help" class="field-help">Gunakan tautan affiliate Shopee. Kosongkan jika belum tersedia.</p>
<p class="field-help">Rating Shopee berasal dari data agregat marketplace, bukan perhitungan dari sampel review yang ditampilkan.</p>
@if($product)<a class="text-link" href="{{ route('preview.reviews', $product['id']) }}">Kelola referensi review<x-icon name="arrow"/>
</a>
@endif</section>
</div>
<aside class="form-sidebar">
<section class="panel publish-panel">
<h2>Publikasi</h2>
<p class="muted small">Pilih hasil penyimpanan secara eksplisit.</p>
<label class="choice-card">
<input type="radio" name="is_active" value="0" @checked(!($product['is_active'] ?? false))>
<span>
<strong>Simpan sebagai draft</strong>
<small>Belum terlihat oleh pengunjung.</small>
</span>
</label>
<label class="choice-card">
<input type="radio" name="is_active" value="1" @checked($product['is_active'] ?? false)>
<span>
<strong>Aktifkan produk</strong>
<small>Terlihat di katalog publik.</small>
</span>
</label>
<label class="confirmation">
<input type="checkbox" name="electronics_scope_confirmed" value="1">
<span>Saya mengonfirmasi produk ini termasuk lingkup elektronik.</span>
</label>
<p class="field-help">Wajib dicentang kembali setiap menyimpan produk aktif. Kategori harus aktif.</p>
<hr>
<p class="small muted">Gagal validasi tidak otomatis menyimpan draft. Ubah pilihan dan kirim ulang jika ingin draft.</p>
<button type="submit" class="button full">Periksa & simulasikan simpan</button>
<a class="button button-quiet full" href="{{ route('preview.products') }}">Batal</a>
</section>
</aside>
</div>
<template id="row-specifications">
<div class="repeat-row">
<label class="field">Nama<input name="specifications[INDEX][name]" maxlength="255" placeholder="Nama spesifikasi">
</label>
<label class="field grow">Nilai<input name="specifications[INDEX][value]" placeholder="Nilai spesifikasi">
</label>
<button type="button" class="icon-button danger" data-remove-row aria-label="Hapus baris spesifikasi">×</button>
</div>
</template>
<template id="row-images">
<div class="repeat-row image-row">
<label class="field grow">URL gambar<input name="images[INDEX][image_url]" type="url" data-https placeholder="https://...">
</label>
<label class="field">Teks alternatif<input name="images[INDEX][alt_text]" maxlength="255">
</label>
<label class="field order-field">Urutan<input name="images[INDEX][sort_order]" type="number" min="0" value="0">
</label>
<button type="button" class="icon-button danger" data-remove-row aria-label="Hapus baris gambar">×</button>
</div>
</template>
</form>
@endsection
