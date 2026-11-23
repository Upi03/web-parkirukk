<!DOCTYPE html>
<html lang="id" class="antialiased" 
    x-data="{ 
        darkMode: localStorage.getItem('darkMode') === 'true', 
        sidebarOpen: false, 
        searchOpen: false,
        searchQuery: '',
        searchResults: [],
        searchLoading: false,
        async performSearch() {
            if (this.searchQuery.trim().length < 2) {
                this.searchResults = [];
                return;
            }
            this.searchLoading = true;
            try {
                const res = await fetch('{{ route('quick-search') }}?q=' + encodeURIComponent(this.searchQuery));
                const data = await res.json();
                this.searchResults = data.results || [];
            } catch (err) {
                this.searchResults = [];
            } finally {
                this.searchLoading = false;
            }
        }
    }" 
    x-init="$watch('darkMode', val => { 
        localStorage.setItem('darkMode', val); 
        if (val) { document.documentElement.classList.add('dark'); } 
        else { document.documentElement.classList.remove('dark'); } 
    })" 
    :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SmarPARK - Sistem Manajemen Parkir')</title>

    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS / Tailwind -->
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        :root {
            --primary: 37, 99, 235;
            --bg-body: 248, 250, 252;
            --text-body: 15, 23, 42;
        }
        .dark {
            --bg-body: 15, 23, 42;
            --text-body: 248, 250, 252;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-50 transition-colors duration-200" 
    @keydown.window.ctrl.k.prevent="searchOpen = true" 
    @keydown.window.cmd.k.prevent="searchOpen = true"
    @keydown.window.escape="searchOpen = false">

    <!-- Overlay for mobile sidebar -->
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="sidebarOpen = false" x-cloak></div>

    @include('partials.sidebar')

    <div class="lg:pl-64 flex flex-col min-h-screen">
        @include('partials.topbar')

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <!-- Flash Notification Success -->
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 flex items-center justify-between p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="font-semibold">Berhasil!</span> {{ session('success') }}
                    </div>
                </div>
                <button type="button" @click="show = false" class="text-emerald-600 dark:text-emerald-400 hover:opacity-80">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            @endif

            <!-- Flash Notification Error -->
            @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 flex items-center justify-between p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-sm shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-red-500 text-white flex items-center justify-center shrink-0">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="font-semibold">Perhatian!</span> {{ session('error') }}
                    </div>
                </div>
                <button type="button" @click="show = false" class="text-red-600 dark:text-red-400 hover:opacity-80">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            @endif

            <!-- Validation Errors -->
            @if(isset($errors) && $errors->any())
            <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-sm shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 font-semibold">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500"></i>
                        <span>Terdapat kesalahan pada input Anda:</span>
                    </div>
                    <button type="button" @click="show = false" class="text-red-600 dark:text-red-400 hover:opacity-80">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <ul class="list-disc list-inside space-y-1 text-xs pl-2">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Global Search Modal (Ctrl + K) -->
    <div x-show="searchOpen" class="fixed inset-0 z-50 flex items-start justify-center pt-20 p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.outside="searchOpen = false" class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-in fade-in zoom-in-95 duration-150">
            <!-- Search Input Header -->
            <div class="relative border-b border-slate-100 dark:border-slate-700 px-4 py-3 flex items-center">
                <i data-lucide="search" class="w-5 h-5 text-slate-400 mr-3"></i>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="performSearch()" placeholder="Cari menu, kendaraan, user, atau area parkir..." 
                    class="w-full bg-transparent text-sm focus:outline-none dark:text-white placeholder:text-slate-400"
                    x-ref="searchInput" x-init="$watch('searchOpen', val => { if(val) setTimeout(() => $refs.searchInput.focus(), 50) })">
                <button @click="searchOpen = false" class="text-xs bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-300 px-2 py-1 rounded border border-slate-200 dark:border-slate-600">
                    ESC
                </button>
            </div>

            <!-- Search Content / Quick Links -->
            <div class="max-h-96 overflow-y-auto p-4 space-y-4">
                <!-- If search query typed -->
                <template x-if="searchQuery.trim().length >= 2">
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Hasil Pencarian Database</div>
                        
                        <template x-if="searchLoading">
                            <div class="py-6 text-center text-sm text-slate-400 flex items-center justify-center gap-2">
                                <span class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span> Mencari data...
                            </div>
                        </template>

                        <template x-if="!searchLoading && searchResults.length === 0">
                            <div class="py-6 text-center text-sm text-slate-400">
                                Tidak ada data yang cocok dengan "<span x-text="searchQuery"></span>".
                            </div>
                        </template>

                        <div class="space-y-1" x-show="!searchLoading && searchResults.length > 0">
                            <template x-for="item in searchResults" :key="item.url">
                                <a :href="item.url" class="flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs font-bold">
                                            <span x-text="item.category[0]"></span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-blue-600" x-text="item.title"></div>
                                            <div class="text-xs text-slate-400" x-html="item.subtitle"></div>
                                        </div>
                                    </div>
                                    <span class="text-[11px] px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-500 capitalize" x-text="item.category"></span>
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Navigation Quick Links -->
                <div>
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Pintasan Menu</div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        @if(auth()->user() && auth()->user()->role === 'petugas')
                            <a href="{{ route('petugas.dashboard') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 text-blue-500"></i> Dashboard
                            </a>
                            <a href="{{ route('petugas.transaksi.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="arrow-left-right" class="w-4 h-4 text-emerald-500"></i> Transaksi Parkir
                            </a>
                            <a href="{{ route('petugas.struk.masuk') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="ticket" class="w-4 h-4 text-blue-500"></i> Cetak Struk Masuk
                            </a>
                            <a href="{{ route('petugas.struk.keluar') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="receipt" class="w-4 h-4 text-emerald-500"></i> Cetak Struk Keluar
                            </a>
                            <a href="{{ route('petugas.profil.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="user" class="w-4 h-4 text-indigo-500"></i> Profil Saya
                            </a>
                            <a href="{{ route('petugas.pengaturan.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="settings" class="w-4 h-4 text-slate-500"></i> Pengaturan
                            </a>
                        @elseif(auth()->user() && auth()->user()->role === 'owner')
                            <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="layout-dashboard" class="w-4 h-4 text-blue-500"></i> Dashboard Owner
                            </a>
                            <a href="{{ route('owner.transaksi.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="receipt" class="w-4 h-4 text-emerald-500"></i> Rekap Transaksi
                            </a>
                            <a href="{{ route('owner.laporan.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="file-bar-chart" class="w-4 h-4 text-indigo-500"></i> Laporan Keuangan
                            </a>
                            <a href="{{ route('owner.dashboard.export') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="download" class="w-4 h-4 text-cyan-500"></i> Export Rekap CSV
                            </a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="home" class="w-4 h-4 text-blue-500"></i> Dashboard
                            </a>
                            <a href="{{ route('admin.kendaraan.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="car" class="w-4 h-4 text-emerald-500"></i> Kendaraan
                            </a>
                            <a href="{{ route('admin.area.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="map-pin" class="w-4 h-4 text-amber-500"></i> Area Parkir
                            </a>
                            <a href="{{ route('admin.tarif.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="banknote" class="w-4 h-4 text-purple-500"></i> Tarif Parkir
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="users" class="w-4 h-4 text-cyan-500"></i> Manajemen User
                            </a>
                            <a href="{{ route('admin.log.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="history" class="w-4 h-4 text-rose-500"></i> Log Aktivitas
                            </a>
                            <a href="{{ route('admin.profil.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="user" class="w-4 h-4 text-indigo-500"></i> Profil Saya
                            </a>
                            <a href="{{ route('admin.pengaturan.index') }}" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 text-slate-700 dark:text-slate-200">
                                <i data-lucide="settings" class="w-4 h-4 text-slate-500"></i> Pengaturan
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="px-4 py-2.5 bg-slate-50 dark:bg-slate-700/30 border-t border-slate-100 dark:border-slate-700 text-xs text-slate-400 flex justify-between items-center">
                <span>Tekan <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600">Ctrl</kbd> + <kbd class="px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600">K</kbd> kapan saja</span>
                <span>SmarPARK System</span>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Logout -->
    <x-logout-modal />

    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
    
    @stack('scripts')
</body>
</html>
