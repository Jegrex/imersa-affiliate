@extends('layouts.app')

@section('title', 'Imersa Affiliate - Kurasi Produk Elektronik Pilihan')

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-b from-orange-50 via-white to-slate-50 border-b border-slate-200 py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800 mb-6 border border-orange-200">
                <i class="fa-solid fa-sparkles text-orange-600"></i> Platform Afiliasi Resmi PT Imersa Solusi Teknologi
            </span>
            <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                Rekomendasi <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-amber-600">Gadget & Elektronik</span> Teruji
            </h1>
            <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed">
                Temukan spesifikasi mendalam, ulasan objektif, dan penawaran terbaik untuk laptop, ponsel, audio, dan periferal dengan pengalihan langsung ke toko resmi Shopee.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('catalog.index') }}" class="px-7 py-3.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold text-sm shadow-md shadow-orange-600/30 transition-all hover:-translate-y-0.5">
                    Jelajahi Semua Produk <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                </a>
                <a href="#featured" class="px-7 py-3.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-xl font-bold text-sm transition-all shadow-xs">
                    Lihat Pilihan Unggulan
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Quick Categories Filter Section -->
<section class="py-12 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Kategori Elektronik Pilihan</h2>
                <p class="text-xs text-slate-500">Pilih kategori untuk memfilter katalog gawai terbaik</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-xs font-semibold text-orange-600 hover:text-orange-700 flex items-center gap-1">
                Semua Kategori <i class="fa-solid fa-angle-right text-[10px]"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            @forelse($categories as $category)
                <a href="{{ route('catalog.index', ['category' => $category->slug]) }}"
                    class="group p-4 bg-slate-50 hover:bg-orange-50/50 rounded-2xl border border-slate-200 hover:border-orange-300 transition-all flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-lg font-bold group-hover:scale-110 transition-transform">
                        @if(str_contains(strtolower($category->slug), 'laptop') || str_contains(strtolower($category->slug), 'komputer'))
                            <i class="fa-solid fa-laptop"></i>
                        @elseif(str_contains(strtolower($category->slug), 'phone') || str_contains(strtolower($category->slug), 'tablet'))
                            <i class="fa-solid fa-mobile-screen"></i>
                        @elseif(str_contains(strtolower($category->slug), 'audio') || str_contains(strtolower($category->slug), 'speaker'))
                            <i class="fa-solid fa-headphones"></i>
                        @else
                            <i class="fa-solid fa-microchip"></i>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 group-hover:text-orange-600 transition-colors line-clamp-1">
                            {{ $category->name }}
                        </h3>
                        <span class="text-[11px] text-slate-500">Lihat Rekomendasi &rarr;</span>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-8 text-center text-sm text-slate-500 bg-slate-50 rounded-xl border border-slate-200">
                    Belum ada kategori elektronik aktif yang ditampilkan.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Featured Products Grid -->
<section id="featured" class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <span class="text-xs font-bold text-orange-600 uppercase tracking-wider">Pilihan Tim Imersa</span>
                <h2 class="text-2xl font-bold text-slate-900 mt-1">Produk Unggulan Elektronik</h2>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-sm font-semibold text-orange-600 hover:text-orange-700 flex items-center gap-1.5">
                Buka Katalog Lengkap <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($featuredProducts as $product)
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-col group">
                    <!-- Image Area with Graceful Degradation -->
                    <a href="{{ route('products.show', $product->slug) }}" class="relative aspect-square bg-slate-100 overflow-hidden block">
                        @php
                            $primaryImage = $product->images->first();
                        @endphp
                        @if($primaryImage)
                            <img src="{{ $primaryImage->image_url }}" alt="{{ $primaryImage->alt_text ?? $product->name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 bg-slate-100">
                                <i class="fa-solid fa-image text-3xl mb-2"></i>
                                <span class="text-[11px] font-medium">Foto Belum Tersedia</span>
                            </div>
                        @endif

                        @if($product->label)
                            <span class="absolute top-3 left-3 bg-orange-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider shadow-sm">
                                {{ $product->label }}
                            </span>
                        @endif
                    </a>

                    <!-- Card Body -->
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="text-[11px] font-semibold text-slate-500 mb-1.5 flex items-center justify-between">
                            <span>{{ $product->category->name ?? 'Elektronik' }}</span>
                            @if($product->shopee_rating !== null)
                                <span class="inline-flex items-center gap-1 text-amber-500 font-bold text-xs">
                                    <i class="fa-solid fa-star text-[10px]"></i>
                                    <span>{{ number_format((float)$product->shopee_rating, 2) }}</span>
                                </span>
                            @endif
                        </div>

                        <h3 class="text-sm font-bold text-slate-900 group-hover:text-orange-600 transition-colors line-clamp-2 mb-3">
                            <a href="{{ route('products.show', $product->slug) }}">
                                {{ $product->name }}
                            </a>
                        </h3>

                        <!-- Price Section (Graceful degradation for null) -->
                        <div class="mt-auto pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">Estimasi Harga</span>
                                @if($product->price_amount !== null && $product->price_currency)
                                    <span class="text-base font-extrabold text-slate-900">
                                        {{ $product->price_currency === 'IDR' ? 'Rp ' . number_format((float)$product->price_amount, 0, ',', '.') : $product->price_currency . ' ' . number_format((float)$product->price_amount, 2) }}
                                    </span>
                                @else
                                    <span class="text-xs font-semibold text-slate-400 italic">Lihat di Shopee</span>
                                @endif
                            </div>

                            <a href="{{ route('products.show', $product->slug) }}"
                                class="w-9 h-9 rounded-xl bg-orange-50 hover:bg-orange-600 text-orange-600 hover:text-white flex items-center justify-center transition-colors">
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-sm text-slate-500 bg-white rounded-2xl border border-slate-200">
                    <i class="fa-solid fa-box-open text-3xl text-slate-300 mb-3 block"></i>
                    Belum ada produk unggulan elektronik yang dipublikasikan saat ini.
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Static Section: Kenapa Memilih Kami -->
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="text-xs font-bold text-orange-600 uppercase tracking-wider">Standar Kualitas Imersa</span>
            <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-1">Mengapa Memilih Imersa Affiliate?</h2>
            <p class="text-sm text-slate-600 mt-2">
                Kami menjembatani kebutuhan perangkat elektronik Anda dengan toko terpercaya melalui kurasi yang ketat dan transparan.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80">
                <div class="w-12 h-12 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-certificate"></i>
                </div>
                <h3 class="font-bold text-base text-slate-900 mb-2">100% Kurasi Resmi</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Setiap produk dievaluasi dan dikonfirmasi dalam cakupan elektronik resmi sebelum dipublikasikan ke katalog.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80">
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </div>
                <h3 class="font-bold text-base text-slate-900 mb-2">Tautan Resmi Shopee</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Tombol belanja mengarahkan Anda langsung ke Shopee Mall atau Star Seller terverifikasi tanpa perantara pihak ketiga.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <h3 class="font-bold text-base text-slate-900 mb-2">Spesifikasi Detail</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Kami menyajikan lembar spesifikasi teknis yang rapi dan terstruktur untuk mempermudah perbandingan perangkat Anda.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200/80">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3 class="font-bold text-base text-slate-900 mb-2">Aman & Terpercaya</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Tidak ada pelacakan rahasia atau skema tersembunyi. Keamanan privasi pengunjung adalah prioritas mutlak kami.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
