@extends('layouts.admin')

@section('title', 'Edit Produk: ' . $product->name . ' - Imersa Affiliate')
@section('page_title', 'Edit Produk Elektronik')

@section('content')
<div class="space-y-8 max-w-5xl">
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Produk
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.reviews.index', $product->id) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-2 transition-colors">
                <i class="fa-solid fa-comments text-xs"></i> Kelola Ulasan ({{ $product->reviews->count() }})
            </a>
            @if($product->is_active)
                <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold flex items-center gap-2 transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> Pratinjau Publik
                </a>
            @endif
        </div>
    </div>

    <!-- DEDICATED CARD: Verifikasi & Persetujuan Tautan Afiliasi (Terpisah dari Form Edit Biasa) -->
    <div class="bg-gradient-to-br from-blue-50/50 via-white to-orange-50/30 p-6 sm:p-8 rounded-3xl border-2 border-blue-200/80 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-blue-100 pb-4">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-700 bg-blue-100/70 px-2.5 py-1 rounded-md">
                    Security & Governance Boundary
                </span>
                <h2 class="text-base font-bold text-slate-900 mt-2 flex items-center gap-2">
                    <i class="fa-solid fa-shield-check text-blue-600"></i> Persetujuan Tautan Afiliasi Shopee
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Sesuai kontrak API & Dokumen Keamanan §9, status afiliasi diatur melalui kebijakan persetujuan terpisah (tidak melalui form generic edit).
                </p>
            </div>
            <div>
                @if($product->affiliate_url_status === 'approved')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-300">
                        <i class="fa-solid fa-circle-check"></i> Status: Disetujui (CTA Aktif)
                    </span>
                @elseif($product->affiliate_url_status === 'pending')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-300">
                        <i class="fa-solid fa-clock"></i> Status: Menunggu Verifikasi
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-300">
                        <i class="fa-solid fa-circle-xmark"></i> Status: Ditolak
                    </span>
                @endif
            </div>
        </div>

        @if($product->affiliate_url_status === 'approved' && $product->affiliate_url_approved_at)
            <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-100 text-[11px] text-blue-900 flex flex-wrap items-center gap-4">
                <span>Disetujui pada: <strong>{{ \Carbon\Carbon::parse($product->affiliate_url_approved_at)->format('d M Y, H:i') }} UTC</strong></span>
                <span>Provenance: <strong>{{ $product->affiliate_url_provenance ?? 'internal_manual' }}</strong></span>
                @if($product->affiliate_url)
                    <span class="truncate max-w-md">Target: <strong class="font-mono">{{ $product->affiliate_url }}</strong></span>
                @endif
            </div>
        @endif

        <!-- Approval / Rejection Form -->
        <form action="{{ route('admin.products.affiliate-approval.store', $product->id) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="aff_url" class="block text-xs font-bold text-slate-700 mb-1.5">Tautan URL Afiliasi Shopee</label>
                <input type="url" id="aff_url" name="affiliate_url" value="{{ old('affiliate_url', $product->affiliate_url) }}"
                    placeholder="https://shopee.co.id/universal-link/..."
                    class="w-full bg-white border border-slate-300 rounded-xl py-2.5 px-3 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-[10px] text-slate-500 mt-1">Harus menggunakan HTTPS, port 443, dan domain host Shopee yang disetujui dalam konfigurasi.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="aff_prov" class="block text-xs font-bold text-slate-700 mb-1.5">Sumber Provenance</label>
                    <select id="aff_prov" name="affiliate_url_provenance"
                        class="w-full bg-white border border-slate-300 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="internal_manual" {{ old('affiliate_url_provenance', $product->affiliate_url_provenance) === 'internal_manual' ? 'selected' : '' }}>Internal Manual</option>
                        <option value="import_candidate" {{ old('affiliate_url_provenance', $product->affiliate_url_provenance) === 'import_candidate' ? 'selected' : '' }}>Kandidat Impor</option>
                        <option value="approved_other" {{ old('affiliate_url_provenance', $product->affiliate_url_provenance) === 'approved_other' ? 'selected' : '' }}>Sumber Lain Disetujui</option>
                    </select>
                </div>

                <div>
                    <label for="aff_source_field" class="block text-xs font-bold text-slate-700 mb-1.5">Kolom Sumber (Opsional)</label>
                    <input type="text" id="aff_source_field" name="affiliate_url_source_field" value="{{ old('affiliate_url_source_field', $product->affiliate_url_source_field) }}"
                        placeholder="Misal: LINKORDER"
                        class="w-full bg-white border border-slate-300 rounded-xl py-2.5 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" name="action" value="approve"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-600/30 flex items-center gap-2 cursor-pointer transition-all">
                    <i class="fa-solid fa-check"></i> Setujui & Aktifkan CTA
                </button>
                <button type="submit" name="action" value="reject"
                    class="px-5 py-2.5 bg-red-100 hover:bg-red-200 text-red-700 rounded-xl text-xs font-bold flex items-center gap-2 cursor-pointer transition-all">
                    <i class="fa-solid fa-ban"></i> Tolak Tautan Ini
                </button>
            </div>
        </form>
    </div>

    <!-- MAIN FORM: Edit Informasi Produk -->
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- Section 1: Informasi Pokok & Status -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-6">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-info-circle text-orange-600"></i> Informasi Pokok & Status Katalog
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nama Produk Elektronik <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @error('name')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div>
                    <label for="slug" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Slug URL (Unik) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="slug" name="slug" value="{{ old('slug', $product->slug) }}" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @error('slug')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category_id" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Kategori Elektronik <span class="text-red-500" id="cat-required-mark">*</span>
                    </label>
                    <select id="category_id" name="category_id"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">-- Tanpa Kategori (Hanya Draf) --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }} {{ !$category->is_active ? '⚠️ (Nonaktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Label -->
                <div>
                    <label for="label" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Label Promosi / Badge (Opsional)
                    </label>
                    <input type="text" id="label" name="label" value="{{ old('label', $product->label) }}"
                        placeholder="Contoh: Rekomendasi Utama"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @error('label')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status Radio Selector -->
            <div class="pt-4 border-t border-slate-100">
                <span class="block text-xs font-bold text-slate-700 mb-2">
                    Status Visibilitas Katalog <span class="text-red-500">*</span>
                </span>
                <div class="flex items-center gap-6 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-800">
                        <input type="radio" name="is_active" value="1" {{ old('is_active', $product->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                            class="text-orange-600 focus:ring-orange-500" onchange="toggleScopeConfirmation(true)">
                        <span><strong>Aktif</strong> (Tampil di Katalog Publik)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-800">
                        <input type="radio" name="is_active" value="0" {{ old('is_active', $product->is_active ? '1' : '0') === '0' ? 'checked' : '' }}
                            class="text-orange-600 focus:ring-orange-500" onchange="toggleScopeConfirmation(false)">
                        <span><strong>Draf</strong> (Disembunyikan dari Publik)</span>
                    </label>
                </div>
                @error('is_active')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- ADR-009 Mandatory Electronics Scope Confirmation Checkbox -->
            <div id="scope-confirmation-box" class="p-4 bg-orange-50/70 border border-orange-200 rounded-2xl">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" id="electronics_scope_confirmed" name="electronics_scope_confirmed" value="accepted"
                        {{ old('electronics_scope_confirmed') === 'accepted' ? 'checked' : '' }}
                        class="mt-0.5 rounded text-orange-600 focus:ring-orange-500">
                    <div class="text-xs text-slate-800">
                        <strong class="text-orange-950 font-bold block mb-0.5">Konfirmasi Cakupan Domain Elektronik (ADR-009 Gate)</strong>
                        <span>Saya mengonfirmasi bahwa produk ini tetap tergolong dalam cakupan elektronik resmi Imersa dan layak aktif di katalog publik.</span>
                    </div>
                </label>
                @error('electronics_scope_confirmed')
                    <p class="text-red-600 font-semibold text-[11px] mt-2 ml-7">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Section 2: Toko & Harga Resmi -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-6">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-store text-orange-600"></i> Informasi Toko, Harga, & Ulasan Eksternal
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="store_name" class="block text-xs font-bold text-slate-700 mb-1.5">Nama Toko Resmi</label>
                    <input type="text" id="store_name" name="store_name" value="{{ old('store_name', $product->store_name) }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>

                <div>
                    <label for="store_url" class="block text-xs font-bold text-slate-700 mb-1.5">URL Toko (Shopee)</label>
                    <input type="url" id="store_url" name="store_url" value="{{ old('store_url', $product->store_url) }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @error('store_url')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price_amount" class="block text-xs font-bold text-slate-700 mb-1.5">Estimasi Harga</label>
                    <input type="number" step="0.01" min="0" id="price_amount" name="price_amount" value="{{ old('price_amount', $product->price_amount) }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @error('price_amount')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price_currency" class="block text-xs font-bold text-slate-700 mb-1.5">Mata Uang (ISO 3-Huruf)</label>
                    <input type="text" maxlength="3" id="price_currency" name="price_currency" value="{{ old('price_currency', $product->price_currency ?? 'IDR') }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-mono uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @error('price_currency')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="shopee_rating" class="block text-xs font-bold text-slate-700 mb-1.5">Rating Shopee (0.00 - 5.00)</label>
                    <input type="number" step="0.01" min="0" max="5" id="shopee_rating" name="shopee_rating" value="{{ old('shopee_rating', $product->shopee_rating) }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @error('shopee_rating')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="shopee_review_count" class="block text-xs font-bold text-slate-700 mb-1.5">Jumlah Ulasan Shopee</label>
                    <input type="number" min="0" id="shopee_review_count" name="shopee_review_count" value="{{ old('shopee_review_count', $product->shopee_review_count) }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @error('shopee_review_count')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="review_video_url" class="block text-xs font-bold text-slate-700 mb-1.5">URL Video Ulasan (YouTube)</label>
                    <input type="url" id="review_video_url" name="review_video_url" value="{{ old('review_video_url', $product->review_video_url) }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    @error('review_video_url')
                        <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section 3: Deskripsi Produk (Tersanitasi) -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-4">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <i class="fa-solid fa-align-left text-orange-600"></i> Deskripsi Produk (HTML Aman)
            </h2>
            <div>
                <textarea id="description_html" name="description_html" rows="6"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">{{ old('description_html', $product->description_html) }}</textarea>
                @error('description_html')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Section 4: Galeri Foto Produk -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-images text-orange-600"></i> Galeri Foto Produk (Tautan Eksternal)
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tautan gambar eksternal tervalidasi allowlist.</p>
                </div>
                <button type="button" onclick="addImageRow()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">
                    + Tambah Foto
                </button>
            </div>

            <div id="images-container" class="space-y-3">
                @php $imgCount = 0; @endphp
                @forelse($product->images as $img)
                    <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 image-row">
                        <input type="hidden" name="images[{{ $imgCount }}][id]" value="{{ $img->id }}">
                        <div class="col-span-7">
                            <input type="url" name="images[{{ $imgCount }}][image_url]" value="{{ $img->image_url }}" required
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div class="col-span-3">
                            <input type="text" name="images[{{ $imgCount }}][alt_text]" value="{{ $img->alt_text }}"
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <input type="number" name="images[{{ $imgCount }}][sort_order]" value="{{ $img->sort_order }}"
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                            <button type="button" onclick="this.closest('.image-row').remove()" class="text-red-500 hover:text-red-700 text-xs px-1">&times;</button>
                        </div>
                    </div>
                    @php $imgCount++; @endphp
                @empty
                    <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 image-row">
                        <div class="col-span-7">
                            <input type="url" name="images[0][image_url]" placeholder="URL Foto (https://...)"
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div class="col-span-3">
                            <input type="text" name="images[0][alt_text]" placeholder="Teks Alt Foto"
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div class="col-span-2">
                            <input type="number" name="images[0][sort_order]" value="0"
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Section 5: Lembar Spesifikasi Teknis -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-table-list text-orange-600"></i> Spesifikasi Teknis Perangkat
                    </h2>
                </div>
                <button type="button" onclick="addSpecRow()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">
                    + Tambah Spesifikasi
                </button>
            </div>

            <div id="specs-container" class="space-y-3">
                @php $specCount = 0; @endphp
                @forelse($product->specifications as $spec)
                    <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 spec-row">
                        <input type="hidden" name="specifications[{{ $specCount }}][id]" value="{{ $spec->id }}">
                        <div class="col-span-4">
                            <input type="text" name="specifications[{{ $specCount }}][name]" value="{{ $spec->name }}" required
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div class="col-span-6">
                            <input type="text" name="specifications[{{ $specCount }}][value]" value="{{ $spec->value }}" required
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <input type="number" name="specifications[{{ $specCount }}][sort_order]" value="{{ $spec->sort_order }}"
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                            <button type="button" onclick="this.closest('.spec-row').remove()" class="text-red-500 hover:text-red-700 text-xs px-1">&times;</button>
                        </div>
                    </div>
                    @php $specCount++; @endphp
                @empty
                    <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 spec-row">
                        <div class="col-span-4">
                            <input type="text" name="specifications[0][name]" placeholder="Nama Spesifikasi"
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div class="col-span-6">
                            <input type="text" name="specifications[0][value]" placeholder="Nilai Parameter"
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                        <div class="col-span-2">
                            <input type="number" name="specifications[0][sort_order]" value="0"
                                class="w-full bg-white border border-slate-200 rounded-lg py-2 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Submit Section -->
        <div class="pt-4 flex items-center justify-end gap-4">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition-colors">
                Batal
            </a>
            <button type="submit" class="px-8 py-3.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-orange-600/30 transition-all cursor-pointer">
                Perbarui Informasi Produk
            </button>
        </div>
    </form>
</div>

<script>
    function toggleScopeConfirmation(isActive) {
        const box = document.getElementById('scope-confirmation-box');
        const chk = document.getElementById('electronics_scope_confirmed');
        const catMark = document.getElementById('cat-required-mark');
        if (isActive) {
            box.style.display = 'block';
            chk.required = true;
            catMark.style.display = 'inline';
        } else {
            box.style.display = 'none';
            chk.required = false;
            chk.checked = false;
            catMark.style.display = 'none';
        }
    }

    let imageIndex = {{ max($imgCount ?? 1, 1) + 10 }};
    function addImageRow() {
        const container = document.getElementById('images-container');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-12 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 image-row';
        div.innerHTML = `
            <div class="col-span-7">
                <input type="url" name="images[${imageIndex}][image_url]" placeholder="URL Foto (https://...)" required
                    class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-orange-500">
            </div>
            <div class="col-span-3">
                <input type="text" name="images[${imageIndex}][alt_text]" placeholder="Teks Alt Foto"
                    class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
            </div>
            <div class="col-span-2 flex items-center gap-2">
                <input type="number" name="images[${imageIndex}][sort_order]" value="${imageIndex}"
                    class="w-full bg-white border border-slate-200 rounded-lg py-2 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                <button type="button" onclick="this.closest('.image-row').remove()" class="text-red-500 hover:text-red-700 text-xs px-1">&times;</button>
            </div>
        `;
        container.appendChild(div);
        imageIndex++;
    }

    let specIndex = {{ max($specCount ?? 1, 1) + 10 }};
    function addSpecRow() {
        const container = document.getElementById('specs-container');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-12 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 spec-row';
        div.innerHTML = `
            <div class="col-span-4">
                <input type="text" name="specifications[${specIndex}][name]" placeholder="Nama Spesifikasi"
                    class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
            </div>
            <div class="col-span-6">
                <input type="text" name="specifications[${specIndex}][value]" placeholder="Nilai Parameter"
                    class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
            </div>
            <div class="col-span-2 flex items-center gap-2">
                <input type="number" name="specifications[${specIndex}][sort_order]" value="${specIndex}"
                    class="w-full bg-white border border-slate-200 rounded-lg py-2 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                <button type="button" onclick="this.closest('.spec-row').remove()" class="text-red-500 hover:text-red-700 text-xs px-1">&times;</button>
            </div>
        `;
        container.appendChild(div);
        specIndex++;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const isActiveChecked = document.querySelector('input[name="is_active"]:checked');
        toggleScopeConfirmation(!isActiveChecked || isActiveChecked.value === '1');
    });
</script>
@endsection
