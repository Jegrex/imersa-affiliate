@extends('layouts.app')

@section('title', 'Katalog Produk Elektronik - Imersa Affiliate')

@section('content')
<div class="bg-slate-50 py-10 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header & Breadcrumb -->
        <nav class="flex items-center text-xs text-slate-500 mb-4 gap-2">
            <a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Beranda</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Katalog Elektronik</span>
        </nav>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Katalog Produk Elektronik</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Menampilkan rekomendasi perangkat resmi pilihan PT Imersa Solusi Teknologi
                </p>
            </div>
            <div class="text-xs text-slate-500 bg-white px-3 py-1.5 rounded-lg border border-slate-200 self-start md:self-auto">
                Total Ditemukan: <strong class="text-slate-800">{{ $products->total() }}</strong> Produk
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Filters -->
        <aside class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                <h2 class="text-sm font-bold text-slate-900 mb-4 flex items-center justify-between">
                    <span>Filter Kategori</span>
                    @if($categoryParam || $searchQuery)
                        <a href="{{ route('catalog.index') }}" class="text-[11px] font-medium text-orange-600 hover:underline">
                            Reset Filter
                        </a>
                    @endif
                </h2>

                <div class="space-y-1.5">
                    <a href="{{ route('catalog.index', array_filter(['q' => $searchQuery])) }}"
                        class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ empty($categoryParam) ? 'bg-orange-50 text-orange-600' : 'text-slate-600 hover:bg-slate-50' }}">
                        <span>Semua Kategori</span>
                        @if(empty($categoryParam))
                            <i class="fa-solid fa-check text-[10px]"></i>
                        @endif
                    </a>

                    @foreach($categories as $category)
                        <a href="{{ route('catalog.index', array_filter(['category' => $category->slug, 'q' => $searchQuery])) }}"
                            class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ $categoryParam === $category->slug ? 'bg-orange-50 text-orange-600' : 'text-slate-600 hover:bg-slate-50' }}">
                            <span class="truncate">{{ $category->name }}</span>
                            @if($categoryParam === $category->slug)
                                <i class="fa-solid fa-check text-[10px]"></i>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Keyword Search Widget in Sidebar -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
                <h2 class="text-sm font-bold text-slate-900 mb-3">Pencarian Nama</h2>
                <form action="{{ route('catalog.index') }}" method="GET" class="space-y-3">
                    @if($categoryParam)
                        <input type="hidden" name="category" value="{{ $categoryParam }}">
                    @endif
                    <div class="relative">
                        <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Cari nama perangkat..."
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition-colors">
                        Terapkan Pencarian
                    </button>
                </form>
            </div>
        </aside>

        <!-- Product Grid Main Area -->
        <div class="lg:col-span-3">
            @if($searchQuery || $categoryParam)
                <div class="mb-6 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-slate-500">Filter Aktif:</span>
                    @if($categoryParam)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-200 text-slate-800 font-medium">
                            Kategori: {{ $categoryParam }}
                            <a href="{{ route('catalog.index', array_filter(['q' => $searchQuery])) }}" class="text-slate-500 hover:text-red-500">&times;</a>
                        </span>
                    @endif
                    @if($searchQuery)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-200 text-slate-800 font-medium">
                            Pencarian: "{{ $searchQuery }}"
                            <a href="{{ route('catalog.index', array_filter(['category' => $categoryParam])) }}" class="text-slate-500 hover:text-red-500">&times;</a>
                        </span>
                    @endif
                </div>
            @endif

            @if($products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    @foreach($products as $product)
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-col group">
                            <!-- Image with Graceful Degradation -->
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
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-10">
                    {{ $products->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto mb-4">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Tidak ada produk yang cocok</h3>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-md mx-auto">
                        Kami tidak menemukan produk elektronik yang sesuai dengan filter atau kata kunci pencarian Anda.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('catalog.index') }}" class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-semibold shadow-sm transition-colors">
                            Tampilkan Semua Produk
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
