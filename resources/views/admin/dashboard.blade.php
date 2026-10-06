@extends('layouts.admin')

@section('title', 'Dashboard Administrator - Imersa Affiliate')
@section('page_title', 'Ringkasan Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Top KPI Cards (Zero-safe numbers) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Total Klik Afiliasi -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-2xl shrink-0">
                <i class="fa-solid fa-arrow-pointer"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Klik Afiliasi</span>
                <span class="text-3xl font-extrabold text-slate-900 mt-1 block">
                    {{ number_format($metrics['total_clicks']) }}
                </span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">Tercatat menuju Shopee</span>
            </div>
        </div>

        <!-- Card 2: Produk Aktif -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl shrink-0">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Produk Aktif</span>
                <span class="text-3xl font-extrabold text-slate-900 mt-1 block">
                    {{ number_format($metrics['active_products']) }}
                </span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">Dari {{ number_format($metrics['total_products']) }} total produk</span>
            </div>
        </div>

        <!-- Card 3: Total Kategori -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-2xl shrink-0">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Kategori</span>
                <span class="text-3xl font-extrabold text-slate-900 mt-1 block">
                    {{ number_format($metrics['total_categories']) }}
                </span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">Taksonomi elektronik</span>
            </div>
        </div>

        <!-- Card 4: Produk dengan Video -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center text-2xl shrink-0">
                <i class="fa-brands fa-youtube"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Dengan Video Review</span>
                <span class="text-3xl font-extrabold text-slate-900 mt-1 block">
                    {{ number_format($metrics['products_with_video']) }}
                </span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ number_format($metrics['products_without_video']) }} tanpa video</span>
            </div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
        <div>
            <h2 class="text-base font-bold">Pusat Kelola Katalog Imersa</h2>
            <p class="text-xs text-slate-400 mt-0.5">Tambah produk baru dengan konfirmasi domain elektronik, kelola ulasan, dan persetujuan tautan afiliasi.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.create') }}" class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-bold shadow-md shadow-orange-600/30 flex items-center gap-2 transition-all">
                <i class="fa-solid fa-plus text-xs"></i> Tambah Produk
            </a>
            <a href="{{ route('admin.categories.index') }}" class="px-5 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold transition-all">
                Kelola Kategori
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Card: Produk Terpopuler Berdasarkan Klik -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Produk Terpopuler (Klik Tertinggi)</h3>
                    <p class="text-xs text-slate-500">Berdasarkan total klik tombol ke Shopee</p>
                </div>
                <a href="{{ route('admin.analytics.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">
                    Lihat Analitik &rarr;
                </a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold uppercase">
                            <th class="py-2.5">Produk</th>
                            <th class="py-2.5 text-center">Status</th>
                            <th class="py-2.5 text-center">Afiliasi</th>
                            <th class="py-2.5 text-right">Total Klik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($metrics['popular_products'] as $product)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 pr-3 font-semibold text-slate-800">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="hover:text-orange-600 transition-colors line-clamp-1">
                                        {{ $product->name }}
                                    </a>
                                </td>
                                <td class="py-3 text-center">
                                    @if($product->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Draf</span>
                                    @endif
                                </td>
                                <td class="py-3 text-center">
                                    @if($product->affiliate_url_status === 'approved')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Disetujui</span>
                                    @elseif($product->affiliate_url_status === 'pending')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Tertunda</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">Ditolak</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right font-bold text-slate-900">
                                    {{ number_format($product->clicks_count) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Belum ada data klik afiliasi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card: Manajemen Produk Terkini -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Produk Terakhir Diperbarui</h3>
                    <p class="text-xs text-slate-500">Pembaruan data katalog terbaru</p>
                </div>
                <a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-orange-600 hover:underline">
                    Semua Produk &rarr;
                </a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold uppercase">
                            <th class="py-2.5">Produk</th>
                            <th class="py-2.5">Kategori</th>
                            <th class="py-2.5 text-center">Status</th>
                            <th class="py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($metrics['recent_products'] as $product)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 pr-3 font-semibold text-slate-800">
                                    <span class="line-clamp-1">{{ $product->name }}</span>
                                </td>
                                <td class="py-3 text-slate-500">
                                    {{ $product->category->name ?? '-' }}
                                </td>
                                <td class="py-3 text-center">
                                    @if($product->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Draf</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="text-orange-600 hover:text-orange-700 font-semibold">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-400">Belum ada produk terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
