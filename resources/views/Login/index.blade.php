<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke SmarPARK</title>
    @vite('resources/css/app.css')
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center p-4 relative overflow-hidden font-sans text-slate-900">
    <!-- Background elements -->
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-blue-100/60 rounded-full blur-3xl -translate-y-1/4 translate-x-1/4 pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-cyan-50/60 rounded-full blur-3xl translate-y-1/4 -translate-x-1/4 pointer-events-none"></div>

    <div class="w-full max-w-md bg-white/80 backdrop-blur-xl border border-white/60 rounded-3xl shadow-2xl shadow-blue-900/5 overflow-hidden relative z-10 animate-[fade-in-up_0.5s_ease-out]">
        
        <!-- Header Logo -->
        <div class="pt-10 pb-6 px-8 text-center">
            <a href="/" class="inline-flex items-center justify-center gap-3 mb-6 group outline-none">
                <div class="bg-blue-600 text-white p-2.5 rounded-xl shadow-md group-hover:bg-blue-700 group-hover:scale-105 transition-all">
                    <i data-lucide="car" class="w-6 h-6"></i>
                </div>
                <span class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-700 to-cyan-500 tracking-tight">SmarPARK</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Selamat Datang</h1>
            <p class="text-slate-500 text-sm">Silakan masuk dengan akun internal Anda.</p>
        </div>

        <!-- Badge Info -->
        <div class="bg-blue-50/70 p-3 mx-8 mb-2 rounded-xl flex items-center gap-3 border border-blue-100/50">
            <i data-lucide="shield-check" class="text-blue-600 w-5 h-5 shrink-0"></i>
            <p class="text-xs text-blue-800 font-medium leading-relaxed">
                Sistem tertutup khusus Admin, Petugas, & Owner. Tidak ada pendaftaran publik.
            </p>
        </div>

        <div class="p-8 pt-4">
            @if (session('status'))
                <div class="mb-6 text-center text-sm font-medium text-green-700 bg-green-50 p-3 rounded-xl border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
                @csrf

                <div class="grid gap-2">
                    <label for="email" class="text-slate-700 font-semibold text-sm">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus tabindex="1" autocomplete="email" placeholder="nama@smarpark.local" class="h-12 w-full px-4 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-colors @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <label for="password" class="text-slate-700 font-semibold text-sm">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" tabindex="5" class="text-xs text-blue-600 hover:text-blue-700 hover:underline font-medium">Lupa password?</a>
                        @endif
                    </div>
                    <input id="password" type="password" name="password" required tabindex="2" autocomplete="current-password" placeholder="••••••••" class="h-12 w-full px-4 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none transition-colors @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center space-x-3 mt-2">
                    <input id="remember_me" type="checkbox" name="remember" tabindex="3" class="rounded text-blue-600 focus:ring-blue-500 border-slate-300 w-4 h-4 cursor-pointer">
                    <label for="remember_me" class="text-sm text-slate-600 font-medium cursor-pointer">Ingat sesi saya</label>
                </div>

                <button type="submit" tabindex="4" class="w-full h-12 mt-4 bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white rounded-xl shadow-lg shadow-blue-600/25 transition-all font-bold text-base flex items-center justify-center gap-2 cursor-pointer">
                    Masuk ke Sistem
                </button>
            </form>
        </div>
    </div>
    
    <!-- Simple Footer -->
    <div class="absolute bottom-6 left-0 w-full text-center text-slate-400 text-xs font-medium z-10">
        &copy; {{ date('Y') }} SmarPARK Management System. Hak cipta dilindungi.
    </div>

    <!-- Initialize Lucide Icons -->
    <script>
      lucide.createIcons();
    </script>
</body>
</html>
