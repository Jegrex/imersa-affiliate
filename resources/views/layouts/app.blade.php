<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Imersa Affiliate - Rekomendasi Produk Elektronik Pilihan')</title>
    <!-- Tailwind CSS CDN for instant rendering -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 flex flex-col min-h-screen">
    <!-- Top Announcement Bar -->
    <div class="bg-gradient-to-r from-orange-600 via-amber-600 to-orange-500 text-white text-xs py-2 px-4 text-center font-medium">
        <span>⚡ Temukan Rekomendasi Gadget & Elektronik Terbaik | Tautan Terverifikasi Resmi Shopee</span>
    </div>

    <!-- Navigation Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">
                <!-- Brand / Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 group">
                    <div class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-white font-bold text-xl shadow-md shadow-orange-500/30 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <div class="font-extrabold text-xl tracking-tight text-slate-900 leading-none">
                            IMERSA <span class="text-orange-600">AFFILIATE</span>
                        </div>
                        <div class="text-[10px] text-slate-500 tracking-wider uppercase font-semibold mt-1">PT Imersa Solusi Teknologi</div>
                    </div>
                </a>

                <!-- Search Bar (Desktop) -->
                <div class="hidden md:flex flex-1 max-w-xl mx-4">
                    <form action="{{ route('catalog.index') }}" method="GET" class="w-full relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari laptop, smartphone, audio pilihan..."
                            class="w-full bg-slate-100 border border-slate-200 rounded-full py-2.5 pl-11 pr-24 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-sm"></i>
                        </div>
                        <button type="submit" class="absolute right-1.5 top-1.5 bottom-1.5 px-4 bg-orange-600 hover:bg-orange-700 text-white rounded-full text-xs font-semibold shadow-sm transition-colors">
                            Cari
                        </button>
                    </form>
                </div>

                <!-- Nav Links -->
                <nav class="flex items-center gap-4">
                    <a href="{{ route('home') }}" class="text-sm font-semibold {{ request()->routeIs('home') ? 'text-orange-600' : 'text-slate-700 hover:text-orange-600' }} transition-colors">
                        Beranda
                    </a>
                    <a href="{{ route('catalog.index') }}" class="text-sm font-semibold {{ request()->routeIs('catalog.*') ? 'text-orange-600' : 'text-slate-700 hover:text-orange-600' }} transition-colors">
                        Katalog Elektronik
                    </a>
                    <a href="{{ route('admin.login') }}" class="hidden sm:inline-flex items-center gap-2 text-xs font-medium text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg border border-slate-200 transition-colors">
                        <i class="fa-solid fa-lock text-[10px]"></i> Portal Admin
                    </a>
                </nav>
            </div>

            <!-- Mobile Search Bar -->
            <div class="md:hidden pb-3">
                <form action="{{ route('catalog.index') }}" method="GET" class="w-full relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk elektronik..."
                        class="w-full bg-slate-100 border border-slate-200 rounded-full py-2 pl-10 pr-20 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <button type="submit" class="absolute right-1 top-1 bottom-1 px-3 bg-orange-600 text-white rounded-full text-[11px] font-semibold">
                        Cari
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 mt-20 pt-16 pb-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-slate-800 text-sm">
                <!-- Col 1: About -->
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-orange-500 flex items-center justify-center text-white font-bold text-base">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <span class="font-extrabold text-lg text-white tracking-tight">IMERSA AFFILIATE</span>
                    </div>
                    <p class="text-slate-400 leading-relaxed max-w-md text-xs sm:text-sm">
                        Katalog kurasi produk elektronik berkualitas milik PT Imersa Solusi Teknologi. Kami membantu Anda menemukan laptop, ponsel, audio, dan gawai terbaik dengan ulasan tepercaya dan tautan resmi ke Shopee.
                    </p>
                    <div class="mt-4 p-3 bg-slate-800/60 rounded-xl border border-slate-700/50 text-[11px] text-slate-400">
                        <strong class="text-slate-300">Transparansi Afiliasi:</strong> Sistem ini tidak memproses pembayaran langsung dan tidak menjual barang fisik. Seluruh transaksi pembelian dilakukan secara aman di platform resmi Shopee melalui tautan terverifikasi.
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-4">Navigasi</h3>
                    <ul class="space-y-2.5 text-xs sm:text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-orange-400 transition-colors">Beranda</a></li>
                        <li><a href="{{ route('catalog.index') }}" class="hover:text-orange-400 transition-colors">Semua Produk Elektronik</a></li>
                        <li><a href="{{ route('admin.login') }}" class="hover:text-orange-400 transition-colors">Masuk Dashboard Admin</a></li>
                    </ul>
                </div>

                <!-- Col 3: PT Info -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-4">PT Imersa Solusi Teknologi</h3>
                    <p class="text-xs text-slate-400 leading-relaxed mb-3">
                        Penyedia solusi kurasi teknologi dan platform afiliasi terpercaya.
                    </p>
                    <div class="flex items-center gap-3 text-slate-400 text-xs">
                        <i class="fa-solid fa-shield-halved text-orange-400 text-sm"></i>
                        <span>100% Produk Elektronik Asli</span>
                    </div>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <div>
                    &copy; {{ date('Y') }} PT Imersa Solusi Teknologi. Seluruh hak cipta dilindungi undang-undang.
                </div>
                <div class="text-[11px] text-slate-500">
                    Katalog Elektronik Berbasis Afiliasi Shopee MVP
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
