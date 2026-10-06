@extends('layouts.admin')

@section('title', 'Laporan & Analitik - Imersa Affiliate')
@section('page_title', 'Laporan & Analitik Afiliasi')

@section('content')
<div class="space-y-8">
    <!-- Filter Periode Form -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-slate-900">Rentang Waktu Laporan</h2>
            <p class="text-xs text-slate-500">Maksimal rentang analisis 366 hari sesuai spesifikasi arsitektur.</p>
        </div>

        <form action="{{ route('admin.analytics.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div>
                <label for="from" class="sr-only">Dari Tanggal</label>
                <input type="date" id="from" name="from" value="{{ $analytics['from'] }}"
                    class="bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <span class="text-xs text-slate-400">s/d</span>
            <div>
                <label for="to" class="sr-only">Sampai Tanggal</label>
                <input type="date" id="to" name="to" value="{{ $analytics['to'] }}"
                    class="bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition-colors">
                Terapkan Periode
            </button>
        </form>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-arrow-pointer"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-semibold block">Total Klik Afiliasi Periode Ini</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-0.5 block">
                    {{ number_format($analytics['total_clicks']) }}
                </span>
                <span class="text-[10px] text-slate-400">{{ $analytics['from'] }} s/d {{ $analytics['to'] }}</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-brands fa-youtube"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-semibold block">Produk Memiliki Video</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-0.5 block">
                    {{ number_format($analytics['products_with_video']) }}
                </span>
                <span class="text-[10px] text-slate-400">Rasio ulasan video terverifikasi</span>
            </div>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-xs flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl shrink-0">
                <i class="fa-solid fa-video-slash"></i>
            </div>
            <div>
                <span class="text-xs text-slate-500 font-semibold block">Produk Tanpa Video</span>
                <span class="text-2xl font-extrabold text-slate-900 mt-0.5 block">
                    {{ number_format($analytics['products_without_video']) }}
                </span>
                <span class="text-[10px] text-slate-400">Perlu penambahan ulasan visual</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Daily Clicks Table -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex flex-col">
            <h3 class="text-sm font-bold text-slate-900 mb-1">Distribusi Klik Harian</h3>
            <p class="text-xs text-slate-500 mb-4">Jumlah kunjungan diarahkan ke Shopee per hari</p>

            <div class="overflow-y-auto max-h-96 flex-1">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold">
                            <th class="py-2.5">Tanggal</th>
                            <th class="py-2.5 text-right">Jumlah Klik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($analytics['daily_clicks'] as $day)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 font-mono text-slate-700">
                                    {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}
                                </td>
                                <td class="py-2.5 text-right font-bold text-slate-900">
                                    {{ number_format($day->count) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-8 text-center text-slate-400">
                                    Tidak ada data klik pada rentang tanggal ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Products in Period -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-xs p-6 flex flex-col">
            <h3 class="text-sm font-bold text-slate-900 mb-1">10 Produk Paling Diminati</h3>
            <p class="text-xs text-slate-500 mb-4">Produk dengan konversi klik tertinggi dalam periode ini</p>

            <div class="overflow-y-auto max-h-96 flex-1">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-semibold">
                            <th class="py-2.5">Produk</th>
                            <th class="py-2.5 text-right">Klik Dihasilkan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($analytics['top_products'] as $prod)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 pr-2">
                                    <div class="font-semibold text-slate-900 line-clamp-1">{{ $prod->name }}</div>
                                    <span class="text-[10px] text-slate-400 font-mono">/{{ $prod->slug }}</span>
                                </td>
                                <td class="py-2.5 text-right font-bold text-orange-600">
                                    {{ number_format($prod->period_clicks_count) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-8 text-center text-slate-400">
                                    Belum ada produk yang mendapatkan klik pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
