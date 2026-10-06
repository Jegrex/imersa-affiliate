@extends('layouts.admin')

@section('title', 'Tambah Produk Baru - Imersa Affiliate')
@section('page_title', 'Tambah Produk Elektronik')

@section('content')
<form action="{{ route('admin.products.store') }}" method="POST" class="space-y-8 max-w-5xl">
    @csrf

    <!-- Form Top Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Produk
        </a>
        <div class="flex items-center gap-3">
            <button type="submit" class="px-6 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-bold shadow-md shadow-orange-600/30 transition-all cursor-pointer">
                Simpan Produk
            </button>
        </div>
    </div>

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
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    placeholder="Contoh: ASUS Zenbook 14 OLED UX3405"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500"
                    oninput="generateSlug(this.value)">
                @error('name')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Slug -->
            <div>
                <label for="slug" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Slug URL (Unik) <span class="text-red-500">*</span>
                </label>
                <input type="text" id="slug" name="slug" value="{{ old('slug') }}" required
                    placeholder="asus-zenbook-14-oled-ux3405"
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
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}
                            data-active="{{ $category->is_active ? '1' : '0' }}">
                            {{ $category->name }} {{ !$category->is_active ? '⚠️ (Nonaktif - Hanya untuk Draf)' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-500 mt-1">Produk berstatus aktif wajib memilih kategori yang berstatus aktif.</p>
                @error('category_id')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Label -->
            <div>
                <label for="label" class="block text-xs font-bold text-slate-700 mb-1.5">
                    Label Promosi / Badge (Opsional)
                </label>
                <input type="text" id="label" name="label" value="{{ old('label') }}"
                    placeholder="Contoh: Rekomendasi Utama, Best Seller"
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
                    <input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                        class="text-orange-600 focus:ring-orange-500" onchange="toggleScopeConfirmation(true)">
                    <span><strong>Aktif</strong> (Tampil di Katalog Publik)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-800">
                    <input type="radio" name="is_active" value="0" {{ old('is_active') === '0' ? 'checked' : '' }}
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
                    <span>Saya mengonfirmasi bahwa produk ini benar-benar tergolong dalam cakupan kategori elektronik resmi PT Imersa Solusi Teknologi dan layak dipublikasikan ke katalog publik.</span>
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
                <input type="text" id="store_name" name="store_name" value="{{ old('store_name') }}"
                    placeholder="Contoh: ASUS Official Store"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div>
                <label for="store_url" class="block text-xs font-bold text-slate-700 mb-1.5">URL Toko (Shopee)</label>
                <input type="url" id="store_url" name="store_url" value="{{ old('store_url') }}"
                    placeholder="https://shopee.co.id/asus.official"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                <span class="text-[10px] text-slate-400">Wajib HTTPS dan host resmi Shopee.</span>
                @error('store_url')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price_amount" class="block text-xs font-bold text-slate-700 mb-1.5">Estimasi Harga</label>
                <input type="number" step="0.01" min="0" id="price_amount" name="price_amount" value="{{ old('price_amount') }}"
                    placeholder="Contoh: 17999000"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                @error('price_amount')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="price_currency" class="block text-xs font-bold text-slate-700 mb-1.5">Mata Uang (ISO 3-Huruf)</label>
                <input type="text" maxlength="3" id="price_currency" name="price_currency" value="{{ old('price_currency', 'IDR') }}"
                    placeholder="IDR"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-mono uppercase focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                @error('price_currency')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="shopee_rating" class="block text-xs font-bold text-slate-700 mb-1.5">Rating Shopee (0.00 - 5.00)</label>
                <input type="number" step="0.01" min="0" max="5" id="shopee_rating" name="shopee_rating" value="{{ old('shopee_rating') }}"
                    placeholder="4.90"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                <span class="text-[10px] text-slate-400">Rating agregat dari halaman toko Shopee.</span>
                @error('shopee_rating')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="shopee_review_count" class="block text-xs font-bold text-slate-700 mb-1.5">Jumlah Ulasan Shopee</label>
                <input type="number" min="0" id="shopee_review_count" name="shopee_review_count" value="{{ old('shopee_review_count') }}"
                    placeholder="342"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                @error('shopee_review_count')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="md:col-span-2">
                <label for="review_video_url" class="block text-xs font-bold text-slate-700 mb-1.5">URL Video Ulasan (YouTube)</label>
                <input type="url" id="review_video_url" name="review_video_url" value="{{ old('review_video_url') }}"
                    placeholder="https://www.youtube.com/watch?v=..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                @error('review_video_url')
                    <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Section 3: Kandidat Tautan Afiliasi (Perlu Approval Terpisah untuk Aktivasi CTA) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
            <i class="fa-solid fa-link text-orange-600"></i> Kandidat Tautan Afiliasi Shopee
        </h2>
        <p class="text-xs text-slate-500 leading-relaxed">
            Tautan yang dimasukkan di sini akan berstatus <strong>Pending</strong> secara otomatis. Tombol CTA di halaman produk publik hanya akan aktif setelah tautan disetujui (Approved) melalui alur verifikasi terpisah.
        </p>
        <div>
            <label for="affiliate_url" class="block text-xs font-bold text-slate-700 mb-1.5">Tautan Afiliasi Calon</label>
            <input type="url" id="affiliate_url" name="affiliate_url" value="{{ old('affiliate_url') }}"
                placeholder="https://shopee.co.id/universal-link/..."
                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
            @error('affiliate_url')
                <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Section 4: Deskripsi Produk (Tersanitasi) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
            <i class="fa-solid fa-align-left text-orange-600"></i> Deskripsi Produk (HTML Aman)
        </h2>
        <p class="text-xs text-slate-500">
            Input difilter secara ketat dengan Symfony HTML Sanitizer di server (tag yang diizinkan: p, br, strong, b, em, i, ul, ol, li, h2, h3, h4, blockquote).
        </p>
        <div>
            <textarea id="description_html" name="description_html" rows="6"
                placeholder="<p>Tulis deskripsi produk di sini...</p>"
                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">{{ old('description_html') }}</textarea>
            @error('description_html')
                <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Section 5: Galeri Foto Produk (External URL Allowlist) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-images text-orange-600"></i> Galeri Foto Produk (Tautan Eksternal)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Sistem menggunakan URL gambar eksternal tervalidasi (Unsplash/Shopee CDN) bukan file binary.</p>
            </div>
            <button type="button" onclick="addImageRow()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">
                + Tambah Foto
            </button>
        </div>

        <div id="images-container" class="space-y-3">
            <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 image-row">
                <div class="col-span-7">
                    <input type="url" name="images[0][image_url]" placeholder="URL Foto (https://...)" required
                        class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-orange-500">
                </div>
                <div class="col-span-3">
                    <input type="text" name="images[0][alt_text]" placeholder="Teks Alt Foto"
                        class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                </div>
                <div class="col-span-2">
                    <input type="number" name="images[0][sort_order]" value="0" placeholder="Urutan"
                        class="w-full bg-white border border-slate-200 rounded-lg py-2 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                </div>
            </div>
        </div>
    </div>

    <!-- Section 6: Lembar Spesifikasi Teknis -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-table-list text-orange-600"></i> Spesifikasi Teknis Perangkat
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Nama parameter harus unik dalam produk ini.</p>
            </div>
            <button type="button" onclick="addSpecRow()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">
                + Tambah Spesifikasi
            </button>
        </div>

        <div id="specs-container" class="space-y-3">
            <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 spec-row">
                <div class="col-span-4">
                    <input type="text" name="specifications[0][name]" placeholder="Nama (misal: Prosesor)"
                        class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                </div>
                <div class="col-span-6">
                    <input type="text" name="specifications[0][value]" placeholder="Nilai (misal: Intel Core Ultra 7)"
                        class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                </div>
                <div class="col-span-2">
                    <input type="number" name="specifications[0][sort_order]" value="0" placeholder="Urutan"
                        class="w-full bg-white border border-slate-200 rounded-lg py-2 px-2 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Section -->
    <div class="pt-4 flex items-center justify-end gap-4">
        <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition-colors">
            Batal
        </a>
        <button type="submit" class="px-8 py-3.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-orange-600/30 transition-all cursor-pointer">
            Simpan Produk Baru
        </button>
    </div>
</form>

<script>
    function generateSlug(text) {
        const slug = text.toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
        document.getElementById('slug').value = slug;
    }

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

    let imageIndex = 1;
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

    let specIndex = 1;
    function addSpecRow() {
        const container = document.getElementById('specs-container');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-12 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-200 spec-row';
        div.innerHTML = `
            <div class="col-span-4">
                <input type="text" name="specifications[${specIndex}][name]" placeholder="Nama (misal: RAM)"
                    class="w-full bg-white border border-slate-200 rounded-lg py-2 px-3 text-xs focus:outline-none focus:ring-1 focus:ring-orange-500">
            </div>
            <div class="col-span-6">
                <input type="text" name="specifications[${specIndex}][value]" placeholder="Nilai (misal: 16GB LPDDR5)"
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

    // Initialize toggle state
    document.addEventListener('DOMContentLoaded', () => {
        const isActiveChecked = document.querySelector('input[name="is_active"]:checked');
        toggleScopeConfirmation(!isActiveChecked || isActiveChecked.value === '1');
    });
</script>
@endsection
