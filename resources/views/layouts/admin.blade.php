<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard - Imersa Affiliate')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 border-r border-slate-800">
        <!-- Logo -->
        <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-800">
            <div class="w-9 h-9 rounded-lg bg-orange-500 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-orange-500/20">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div>
                <span class="font-bold text-base text-white tracking-tight">IMERSA</span>
                <span class="text-orange-400 font-bold text-base tracking-tight">ADMIN</span>
                <div class="text-[10px] text-slate-400 font-medium">Katalog Afiliasi Elektronik</div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <div class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Menu Utama</div>

            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-orange-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-gauge-high w-5 text-center"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.products.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.products.*') ? 'bg-orange-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-boxes-stacked w-5 text-center"></i>
                <span>Produk Elektronik</span>
            </a>

            <a href="{{ route('admin.categories.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.categories.*') ? 'bg-orange-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-tags w-5 text-center"></i>
                <span>Kategori</span>
            </a>

            <a href="{{ route('admin.analytics.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.analytics.*') ? 'bg-orange-600 text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-chart-line w-5 text-center"></i>
                <span>Laporan & Analitik</span>
            </a>

            <div class="pt-6 px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Akses Luar</div>

            <a href="{{ route('home') }}" target="_blank"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition-colors">
                <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center text-xs"></i>
                <span>Lihat Website Publik</span>
            </a>
        </nav>

        <!-- Current Admin Footer Info -->
        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-orange-400 font-bold text-sm">
                    {{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-white truncate">{{ Auth::guard('admin')->user()->name ?? 'Administrator' }}</p>
                    <p class="text-[11px] text-slate-400 truncate">{{ Auth::guard('admin')->user()->email ?? '' }}</p>
                </div>
            </div>
            <form action="{{ route('admin.logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 px-3 text-xs font-medium text-red-400 hover:text-white hover:bg-red-950/40 rounded-lg border border-red-900/40 transition-colors">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Topbar -->
        <header class="h-20 bg-white border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-30 shadow-xs">
            <div class="flex items-center gap-4">
                <h1 class="text-xl font-bold text-slate-800">@yield('page_title', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Admin Guard Aktif
                </span>
                <span class="text-xs text-slate-400">|</span>
                <span class="text-xs text-slate-500">{{ date('d M Y') }}</span>
            </div>
        </header>

        <!-- Body Container -->
        <main class="flex-1 p-8">
            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm shadow-xs">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm shadow-xs">
                    <i class="fa-solid fa-circle-exclamation text-red-600 text-lg"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3.5 rounded-xl text-sm shadow-xs">
                    <div class="flex items-center gap-2 font-semibold text-amber-800 mb-1">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Mohon periksa kembali formulir Anda:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-1 text-amber-800 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
