<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - Imersa Affiliate</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo Header -->
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-orange-500 flex items-center justify-center text-white font-bold text-2xl mx-auto shadow-lg shadow-orange-500/30 mb-4">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Portal Administrator</h1>
            <p class="text-xs text-slate-400 mt-1">PT Imersa Solusi Teknologi &bull; Imersa Affiliate</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-800 border border-slate-700 rounded-3xl p-8 shadow-2xl">
            @if(session('status'))
                <div class="mb-6 p-3.5 bg-emerald-950/60 border border-emerald-800 text-emerald-300 rounded-xl text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-3.5 bg-red-950/60 border border-red-800 text-red-300 rounded-xl text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                    <span>{{ $errors->first('email') ?: 'Email atau kata sandi tidak valid.' }}</span>
                </div>
            @endif

            <form action="{{ route('admin.login.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-2">Email Administrator</label>
                    <div class="relative">
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="admin@imersa.co.id"
                            class="w-full bg-slate-900/80 border border-slate-700 rounded-xl py-3 pl-11 pr-4 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-envelope text-xs"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 mb-2">Kata Sandi</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            placeholder="••••••••"
                            class="w-full bg-slate-900/80 border border-slate-700 rounded-xl py-3 pl-11 pr-4 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3.5 bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-orange-600/30 transition-all hover:scale-[1.01] active:scale-95 cursor-pointer">
                        Masuk ke Dashboard &rarr;
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-700/60 text-center">
                <a href="{{ route('home') }}" class="text-[11px] text-slate-400 hover:text-orange-400 transition-colors">
                    &larr; Kembali ke Beranda Publik
                </a>
            </div>
        </div>

        <div class="text-center mt-8 text-[11px] text-slate-500">
            Sistem terlindungi &bull; Khusus untuk staf dan administrator internal
        </div>
    </div>
</body>
</html>
