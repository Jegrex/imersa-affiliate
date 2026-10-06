@extends('layouts.admin')

@section('title', 'Ulasan Produk: ' . $product->name . ' - Imersa Affiliate')
@section('page_title', 'Manajemen Ulasan Produk')

@section('content')
<div class="space-y-8 max-w-5xl">
    <!-- Top Action Bar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.products.edit', $product->id) }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Edit Produk
        </a>
        <span class="text-xs font-semibold text-slate-600 bg-white px-3 py-1.5 rounded-xl border border-slate-200">
            Total Ulasan: {{ $reviews->total() }}
        </span>
    </div>

    <!-- Product Summary Card -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-4">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
            @if($product->images->first())
                <img src="{{ $product->images->first()->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            @else
                <i class="fa-solid fa-image text-slate-400 text-2xl"></i>
            @endif
        </div>
        <div class="min-w-0 flex-1">
            <span class="text-xs font-semibold text-orange-600 uppercase">{{ $product->category->name ?? 'Elektronik' }}</span>
            <h1 class="text-lg font-bold text-slate-900 truncate">{{ $product->name }}</h1>
            <div class="flex items-center gap-4 mt-1 text-xs text-slate-500">
                <span>Rating Agregat: <strong class="text-amber-600 font-bold">{{ number_format((float)($product->shopee_rating ?? 0), 2) }} / 5.00</strong></span>
                <span>Ulasan Pembeli Tercatat: <strong class="text-slate-800 font-bold">{{ $reviews->total() }}</strong></span>
            </div>
        </div>
    </div>

    <!-- Add Review Form Card -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs space-y-4">
        <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
            <i class="fa-solid fa-plus-circle text-orange-600"></i> Tambah Ulasan Manual Terverifikasi
        </h2>
        <p class="text-xs text-slate-500">
            Ulasan yang ditambahkan di sini harus bersumber dari review resmi Shopee terverifikasi (provenance wajib diisi).
        </p>

        <form action="{{ route('admin.reviews.store', $product->id) }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="provenance" class="block text-xs font-bold text-slate-700 mb-1">
                        Sumber Provenance <span class="text-red-500">*</span>
                    </label>
                    <select id="provenance" name="provenance" required
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="shopee_manual_reference">Shopee Manual Reference</option>
                        <option value="shopee_import">Shopee Import</option>
                    </select>
                </div>

                <div>
                    <label for="reviewer_name" class="block text-xs font-bold text-slate-700 mb-1">Nama Pembeli</label>
                    <input type="text" id="reviewer_name" name="reviewer_name" value="{{ old('reviewer_name') }}"
                        placeholder="Contoh: Budi Santoso"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>

                <div>
                    <label for="rating_value" class="block text-xs font-bold text-slate-700 mb-1">Rating (1 - 5 Bintang)</label>
                    <input type="number" step="0.1" min="1" max="5" id="rating_value" name="rating_value" value="{{ old('rating_value', '5') }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="avatar_url" class="block text-xs font-bold text-slate-700 mb-1">URL Avatar (Opsional)</label>
                    <input type="url" id="avatar_url" name="avatar_url" value="{{ old('avatar_url') }}"
                        placeholder="https://images.unsplash.com/..."
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>

                <div>
                    <label for="reviewed_at" class="block text-xs font-bold text-slate-700 mb-1">Tanggal Ulasan</label>
                    <input type="date" id="reviewed_at" name="reviewed_at" value="{{ old('reviewed_at', date('Y-m-d')) }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
            </div>

            <div>
                <label for="content" class="block text-xs font-bold text-slate-700 mb-1">Isi Ulasan Pembeli</label>
                <textarea id="content" name="content" rows="3"
                    placeholder="Tulis ulasan pembeli di sini..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">{{ old('content') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Foto Ulasan (Opsional)</label>
                <input type="url" name="images[0][image_url]" placeholder="URL Foto Ulasan (https://...)"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div class="flex items-center justify-between pt-2">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800">
                    <input type="checkbox" name="is_visible" value="1" checked class="rounded text-orange-600 focus:ring-orange-500">
                    <span>Tampilkan ke Publik (is_visible = true)</span>
                </label>

                <button type="submit" class="px-6 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-bold shadow-md shadow-orange-600/30 transition-all cursor-pointer">
                    Simpan Ulasan
                </button>
            </div>
        </form>
    </div>

    <!-- Reviews List Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Daftar Riwayat Ulasan</h2>
            <span class="text-xs text-slate-500">Hapus review = menyembunyikan (is_visible=false), bukan menghapus riwayat fisik.</span>
        </div>

        <div class="divide-y divide-slate-100 text-xs">
            @forelse($reviews as $review)
                <div class="p-6 space-y-3 hover:bg-slate-50/50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @if($review->avatar_url)
                                <img src="{{ $review->avatar_url }}" alt="{{ $review->reviewer_name ?? '' }}" class="w-9 h-9 rounded-full object-cover">
                            @else
                                <div class="w-9 h-9 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-600 text-xs">
                                    {{ strtoupper(substr($review->reviewer_name ?? 'U', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="font-bold text-slate-900 text-xs">
                                    {{ $review->reviewer_name ?? 'Tanpa Nama' }}
                                    <span class="ml-2 font-mono text-[10px] text-slate-400 font-normal">({{ $review->provenance }})</span>
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $review->reviewed_at ? \Carbon\Carbon::parse($review->reviewed_at)->format('d M Y') : 'Tanpa tanggal' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            @if($review->rating_value)
                                <div class="flex items-center gap-1 text-amber-500 font-bold bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200 text-xs">
                                    <i class="fa-solid fa-star text-[10px]"></i>
                                    <span>{{ $review->rating_value }}</span>
                                </div>
                            @endif

                            @if($review->is_visible)
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Tampil Publik
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                    Tersembunyi
                                </span>
                            @endif

                            @if($review->is_visible)
                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST"
                                    onsubmit="return confirm('Sembunyikan ulasan ini dari publik? Riwayat tetap tersimpan utuh di database.');"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white rounded-lg transition-colors cursor-pointer" title="Sembunyikan Ulasan">
                                        <i class="fa-solid fa-eye-slash text-xs"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if($review->content)
                        <p class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">
                            {{ $review->content }}
                        </p>
                    @endif

                    @if($review->images->count() > 0)
                        <div class="flex gap-2">
                            @foreach($review->images as $revImg)
                                <img src="{{ $revImg->image_url }}" alt="Foto ulasan" class="w-14 h-14 rounded-lg object-cover border border-slate-200">
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-slate-400">
                    Belum ada ulasan untuk produk ini.
                </div>
            @endforelse
        </div>

        @if($reviews->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
