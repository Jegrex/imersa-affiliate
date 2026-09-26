@extends('layouts.preview')
@section('content')
<a class="text-link back-link" href="{{ route('preview.products.edit', $product['id']) }}">
<x-icon name="back"/>Kembali ke produk</a>
<x-admin-heading title="Referensi review" :description="$product['name']"/>
<div class="notice">
<x-icon name="info"/>
<p>Ulasan merupakan referensi dari Shopee. Pengunjung tidak mengirim review di website ini.</p>
</div>
<section class="panel">
<h2>Review tersimpan</h2>
@forelse(($empty ? collect() : $reviews->where('product_id', $product['id'])) as $review)<article class="review-card">
<div class="review-header">
<span class="review-avatar">{{ mb_substr($review['name'], 0, 1) }}</span>
<div>
<strong>{{ $review['name'] }}</strong>
<p class="small muted">{{ $review['source'] }}</p>
</div>
<x-status :value="$review['visible'] ? 'active' : 'hidden'"/>
</div>
<p>{{ $review['content'] }}</p>
<div class="inline-actions">
<button class="text-link" data-review-edit data-name="{{ $review['name'] }}" data-content="{{ $review['content'] }}" data-rating="{{ $review['rating'] }}" data-visible="{{ $review['visible'] ? '1' : '0' }}">
<x-icon name="edit"/>Edit referensi</button>
@if($review['visible'])<button class="text-link danger" data-confirm-title="Sembunyikan review?" data-confirm-message="Review tidak tampil di publik, tetapi sumber dan riwayatnya tetap disimpan. Ini simulasi tanpa perubahan data.">Sembunyikan</button>
@endif</div>
</article>
@empty<x-empty title="Belum ada referensi review" description="Tambahkan referensi yang memiliki sumber jelas."/>
@endforelse</section>
<form id="review-form" class="form-narrow review-form" method="post" action="{{ url()->current() }}" data-preview-form>
@csrf<x-form-alerts/>
<section class="panel">
<h2 id="review-form-title">Tambah referensi</h2>
<div class="form-grid">
<label class="field">Nama pengguna <span class="optional">opsional</span>
<input name="reviewer_name" maxlength="255">
</label>
<label class="field">Rating <span class="optional">opsional</span>
<input name="rating_value" type="number" min="1" max="5" step="1" placeholder="1–5">
</label>
<label class="field">Tanggal ulasan<input name="review_date" type="date">
</label>
<label class="field">Sumber <span class="required">*</span>
<select name="source" required>
<option value="shopee_manual_reference">Referensi manual Shopee</option>
<option disabled>Impor Shopee — belum tersedia</option>
</select>
</label>
<label class="field span-2">Referensi asal data <span class="required">*</span>
<textarea name="provenance" required rows="2" placeholder="Sumber yang dapat ditelusuri oleh tim.">
</textarea>
</label>
<label class="field span-2">Isi ulasan<textarea name="content" rows="4">
</textarea>
</label>
<label class="field">URL avatar<input name="avatar_url" type="url" data-https placeholder="https://...">
</label>
<label class="field">Visibilitas<select name="is_visible">
<option value="1">Tampilkan di publik</option>
<option value="0">Sembunyikan</option>
</select>
</label>
</div>
<div class="subheading">
<h3>Foto ulasan</h3>
<button class="text-link" type="button" data-add-row="review-images">
<x-icon name="plus"/>Tambah URL foto</button>
</div>
<div data-collection="review-images">
<div class="repeat-row">
<label class="field grow">URL foto<input type="url" name="images[0][image_url]" data-https placeholder="https://...">
</label>
<button class="icon-button danger" type="button" data-remove-row aria-label="Hapus URL foto">
<x-icon name="trash"/>
</button>
</div>
</div>
<div class="form-actions">
<button class="button button-outline" type="reset">Reset form</button>
<button class="button" type="submit">Periksa & simulasikan simpan</button>
</div>
</section>
<template id="row-review-images">
<div class="repeat-row">
<label class="field grow">URL foto<input name="images[INDEX][image_url]" type="url" data-https placeholder="https://...">
</label>
<button class="icon-button danger" type="button" data-remove-row aria-label="Hapus URL foto">×</button>
</div>
</template>
</form>
@endsection
