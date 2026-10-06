@extends('layouts.admin')

@section('title', 'Manajemen Produk - Imersa Affiliate')
@section('page_title', 'Manajemen Produk Elektronik')

@section('content')
<div class="space-y-6">
    <!-- Top Action & Filter Header -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Search & Filter Form -->
        <form action="{{ route('admin.products.index') }}" method="GET" class="flex-1 flex flex-wrap items-center gap-3">
            <div class="relative min-w-[240px] flex-1">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama produk..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-10 pr-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
            </div>

            <select name="category" class="bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }} {{ !$cat->is_active ? '(Nonaktif)' : '' }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-xs text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Katalog Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Draf (Nonaktif)</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Afiliasi Disetujui</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Afiliasi Tertunda</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Afiliasi Ditolak</option>
            </select>

            <button type="submit" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition-colors">
                Filter
            </button>

            @if(request()->anyFilled(['q', 'category', 'status']))
                <a href="{{ route('admin.products.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-red-600 transition-colors">
                    Reset
                </a>
            @endif
        </form>

        <!-- New Product Button -->
        <a href="{{ route('admin.products.create') }}" class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-bold shadow-md shadow-orange-600/30 flex items-center gap-2 shrink-0 transition-all">
            <i class="fa-solid fa-plus text-xs"></i> Tambah Produk Baru
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-6">Produk</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Harga Estimasi</th>
                        <th class="py-3.5 px-4 text-center">Status Katalog</th>
                        <th class="py-3.5 px-4 text-center">Status Afiliasi</th>
                        <th class="py-3.5 px-4 text-center">Total Klik</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Product Col -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                        @if($product->images->first())
                                            <img src="{{ $product->images->first()->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fa-solid fa-image text-slate-400"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-slate-900 text-xs truncate max-w-xs">{{ $product->name }}</div>
                                        <div class="text-[11px] text-slate-400 font-mono truncate max-w-xs">/{{ $product->slug }}</div>
                                        @if($product->label)
                                            <span class="inline-block mt-1 text-[10px] font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded">
                                                {{ $product->label }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Category Col -->
                            <td class="py-4 px-4 text-slate-600">
                                @if($product->category)
                                    <span class="font-medium {{ !$product->category->is_active ? 'text-amber-600' : '' }}">
                                        {{ $product->category->name }}
                                        @if(!$product->category->is_active)
                                            <span class="text-[10px] block text-amber-500">(Kategori Nonaktif)</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Tanpa Kategori</span>
                                @endif
                            </td>

                            <!-- Price Col -->
                            <td class="py-4 px-4 font-semibold text-slate-800">
                                @if($product->price_amount !== null && $product->price_currency)
                                    {{ $product->price_currency === 'IDR' ? 'Rp ' . number_format((float)$product->price_amount, 0, ',', '.') : $product->price_currency . ' ' . number_format((float)$product->price_amount, 2) }}
                                @else
                                    <span class="text-slate-400 italic font-normal">Tidak Diatur</span>
                                @endif
                            </td>

                            <!-- Catalog Status Col -->
                            <td class="py-4 px-4 text-center">
                                @if($product->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Draf
                                    </span>
                                @endif
                            </td>

                            <!-- Affiliate Status Col -->
                            <td class="py-4 px-4 text-center">
                                @if($product->affiliate_url_status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fa-solid fa-check text-[10px]"></i> Disetujui
                                    </span>
                                @elseif($product->affiliate_url_status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fa-solid fa-clock text-[10px]"></i> Tertunda
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-red-700 border border-red-200">
                                        <i class="fa-solid fa-xmark text-[10px]"></i> Ditolak
                                    </span>
                                @endif
                            </td>

                            <!-- Clicks Col -->
                            <td class="py-4 px-4 text-center font-bold text-slate-800">
                                {{ number_format($product->clicks_count ?? 0) }}
                            </td>

                            <!-- Actions Col -->
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.reviews.index', $product->id) }}"
                                        title="Kelola Ulasan"
                                        class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition-colors">
                                        <i class="fa-solid fa-comments text-xs"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product->id) }}"
                                        title="Edit Produk"
                                        class="p-2 bg-orange-50 hover:bg-orange-600 text-orange-600 hover:text-white rounded-lg transition-colors">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan produk ini? Riwayat klik dan ulasan akan tetap aman di sistem.');"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Nonaktifkan (Soft Delete)"
                                            class="p-2 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white rounded-lg transition-colors cursor-pointer">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-box-open text-3xl mb-2 block"></i>
                                Tidak ada data produk yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-6 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
