@extends('layouts.app')

@section('title', $product->name . ' - Detail Produk Imersa Affiliate')

@section('content')
<div class="bg-slate-50 py-6 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex items-center text-xs text-slate-500 gap-2">
            <a href="{{ route('home') }}" class="hover:text-orange-600 transition-colors">Beranda</a>
            <span>/</span>
            <a href="{{ route('catalog.index') }}" class="hover:text-orange-600 transition-colors">Katalog</a>
            @if($product->category)
                <span>/</span>
                <a href="{{ route('catalog.index', ['category' => $product->category->slug]) }}" class="hover:text-orange-600 transition-colors">
                    {{ $product->category->name }}
                </a>
            @endif
            <span>/</span>
            <span class="text-slate-800 font-semibold truncate max-w-xs">{{ $product->name }}</span>
        </nav>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Left: Image Gallery (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-xs p-4">
                <div class="aspect-square bg-slate-50 rounded-2xl overflow-hidden relative flex items-center justify-center">
                    @php
                        $primaryImage = $product->images->first();
                    @endphp
                    @if($primaryImage)
                        <img id="main-product-image" src="{{ $primaryImage->image_url }}" alt="{{ $primaryImage->alt_text ?? $product->name }}"
                            class="w-full h-full object-contain">
                    @else
                        <div class="flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-solid fa-image text-4xl mb-2"></i>
                            <span class="text-xs">Foto Belum Tersedia</span>
                        </div>
                    @endif

                    @if($product->label)
                        <span class="absolute top-3 left-3 bg-orange-600 text-white text-[11px] font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">
                            {{ $product->label }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Thumbnail Carousel / List -->
            @if($product->images->count() > 1)
                <div class="flex gap-3 overflow-x-auto pb-2">
                    @foreach($product->images as $img)
                        <button type="button" onclick="document.getElementById('main-product-image').src = '{{ $img->image_url }}'"
                            class="w-20 h-20 rounded-xl bg-white border-2 border-slate-200 hover:border-orange-500 overflow-hidden shrink-0 transition-colors p-1">
                            <img src="{{ $img->image_url }}" alt="{{ $img->alt_text ?? '' }}" class="w-full h-full object-contain">
                        </button>
                    @endforeach
                </div>
            @endif

            <!-- YouTube Review Video Widget if available -->
            @if($product->review_video_url)
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                    <h3 class="text-xs font-bold uppercase text-slate-900 mb-2 flex items-center gap-2">
                        <i class="fa-brands fa-youtube text-red-600 text-base"></i> Video Ulasan Terverifikasi
                    </h3>
                    <p class="text-xs text-slate-500 mb-3">Tonton ulasan komprehensif perangkat ini langsung dari kreator resmi.</p>
                    <a href="{{ $product->review_video_url }}" target="_blank" rel="noopener noreferrer"
                        class="w-full py-2.5 px-4 bg-red-50 hover:bg-red-100 text-red-700 rounded-xl text-xs font-bold flex items-center justify-center gap-2 border border-red-200 transition-colors">
                        <i class="fa-solid fa-play text-xs"></i> Tonton di YouTube &rarr;
                    </a>
                </div>
            @endif
        </div>

        <!-- Right: Details, Specs, and CTA (7 cols) -->
        <div class="lg:col-span-7 space-y-8">
            <!-- Header Info -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-semibold text-orange-600 bg-orange-50 px-2.5 py-1 rounded-md border border-orange-100">
                        {{ $product->category->name ?? 'Elektronik' }}
                    </span>
                    @if($product->store_name)
                        <span class="text-xs text-slate-500 flex items-center gap-1">
                            <i class="fa-solid fa-store text-slate-400 text-[10px]"></i> {{ $product->store_name }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
                    {{ $product->name }}
                </h1>

                <!-- Rating -->
                @if($product->shopee_rating !== null)
                    <div class="flex items-center gap-3 mt-3 text-xs">
                        <div class="flex items-center gap-1 text-amber-500 font-bold bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                            <i class="fa-solid fa-star"></i>
                            <span>{{ number_format((float)$product->shopee_rating, 2) }}</span>
                        </div>
                        @if($product->shopee_review_count !== null)
                            <span class="text-slate-500 font-medium">({{ number_format($product->shopee_review_count) }} ulasan pembeli)</span>
                        @endif
                    </div>
                @endif

                <!-- Pricing Box & CTA -->
                <div class="mt-6 p-6 rounded-2xl bg-gradient-to-br from-slate-50 to-orange-50/40 border border-slate-200">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-xs text-slate-500 block font-medium">Perkiraan Harga Toko Resmi</span>
                            @if($product->price_amount !== null && $product->price_currency)
                                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">
                                    {{ $product->price_currency === 'IDR' ? 'Rp ' . number_format((float)$product->price_amount, 0, ',', '.') : $product->price_currency . ' ' . number_format((float)$product->price_amount, 2) }}
                                </div>
                            @else
                                <div class="text-lg font-bold text-slate-600 italic mt-1">
                                    Cek Harga Terbaru di Shopee
                                </div>
                            @endif
                        </div>

                        <!-- CTA Button (Commits click before redirect) -->
                        <div>
                            @if($isCtaEligible)
                                <form action="{{ route('affiliate.redirect', $product->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="w-full sm:w-auto px-8 py-4 bg-orange-600 hover:bg-orange-700 text-white rounded-2xl font-extrabold text-sm shadow-lg shadow-orange-600/30 flex items-center justify-center gap-3 transition-all hover:scale-[1.02] active:scale-95 cursor-pointer">
                                        <i class="fa-solid fa-bag-shopping text-base"></i>
                                        <span>Beli Sekarang di Shopee</span>
                                    </button>
                                </form>
                            @else
                                <div class="px-6 py-3.5 bg-slate-200 text-slate-500 rounded-2xl text-xs font-semibold flex items-center gap-2 border border-slate-300">
                                    <i class="fa-solid fa-circle-pause"></i>
                                    <span>Tautan Pembelian Sedang Menunggu Verifikasi</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-200/80 flex items-center gap-2 text-[11px] text-slate-500">
                        <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                        <span>Pengalihan aman & terlindungi langsung ke halaman toko resmi Shopee.</span>
                    </div>
                </div>
            </div>

            <!-- Specifications Table -->
            @if($product->specifications->count() > 0)
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs">
                    <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-orange-600 text-base"></i> Spesifikasi Teknis
                    </h2>

                    <div class="border border-slate-200 rounded-2xl overflow-hidden divide-y divide-slate-100 text-xs sm:text-sm">
                        @foreach($product->specifications as $spec)
                            <div class="grid grid-cols-3 p-3.5 hover:bg-slate-50 transition-colors">
                                <span class="font-semibold text-slate-600 col-span-1">{{ $spec->name }}</span>
                                <span class="text-slate-900 col-span-2">{{ $spec->value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Product Description -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs">
                <h2 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-orange-600 text-base"></i> Deskripsi Produk
                </h2>

                <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed space-y-3 text-xs sm:text-sm">
                    @if($product->description_html)
                        {!! $product->description_html !!}
                    @else
                        <p class="text-slate-400 italic">Deskripsi detail produk belum ditambahkan.</p>
                    @endif
                </div>
            </div>

            <!-- Customer Reviews Section -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xs">
                <h2 class="text-lg font-bold text-slate-900 mb-6 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-comments text-orange-600 text-base"></i> Ulasan Pembeli Terverifikasi
                    </span>
                    <span class="text-xs text-slate-500 font-normal">({{ $product->reviews->where('is_visible', true)->count() }} ulasan)</span>
                </h2>

                <div class="space-y-6">
                    @forelse($product->reviews->where('is_visible', true) as $review)
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    @if($review->avatar_url)
                                        <img src="{{ $review->avatar_url }}" alt="{{ $review->reviewer_name ?? 'User' }}" class="w-8 h-8 rounded-full object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-300 flex items-center justify-center text-xs font-bold text-slate-600">
                                            {{ strtoupper(substr($review->reviewer_name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">{{ $review->reviewer_name ?? 'Pembeli Terverifikasi' }}</div>
                                        @if($review->reviewed_at)
                                            <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($review->reviewed_at)->format('d M Y') }}</div>
                                        @endif
                                    </div>
                                </div>

                                @if($review->rating_value !== null)
                                    <div class="flex items-center gap-1 text-amber-500 text-xs">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fa-{{ $i <= $review->rating_value ? 'solid' : 'regular' }} fa-star text-[10px]"></i>
                                        @endfor
                                    </div>
                                @endif
                            </div>

                            @if($review->content)
                                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">{{ $review->content }}</p>
                            @endif

                            @if($review->images->count() > 0)
                                <div class="flex gap-2 pt-1">
                                    @foreach($review->images as $revImg)
                                        <img src="{{ $revImg->image_url }}" alt="Review foto" class="w-16 h-16 rounded-xl object-cover border border-slate-200">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs bg-slate-50 rounded-2xl border border-slate-200">
                            Belum ada ulasan pembeli untuk produk ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
