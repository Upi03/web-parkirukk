@php
    $user = auth()->user();
    $role = $user ? $user->role : 'petugas';

    $menus = [];
    if ($role === 'petugas') {
        $menus = [
            ['title' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'petugas.dashboard', 'active' => request()->routeIs('petugas.dashboard')],
            ['title' => 'Transaksi', 'icon' => 'arrow-left-right', 'route' => 'petugas.transaksi.index', 'active' => request()->routeIs('petugas.transaksi.*')],
            ['title' => 'Cetak Struk Masuk', 'icon' => 'ticket', 'route' => 'petugas.struk.masuk', 'active' => request()->routeIs('petugas.struk.masuk') || (request()->routeIs('petugas.struk.index') && request('type', 'masuk') === 'masuk') || (request()->routeIs('petugas.struk.show') && request('type') === 'masuk')],
            ['title' => 'Cetak Struk Keluar', 'icon' => 'receipt', 'route' => 'petugas.struk.keluar', 'active' => request()->routeIs('petugas.struk.keluar') || (request()->routeIs('petugas.struk.index') && request('type') === 'keluar') || (request()->routeIs('petugas.struk.show') && request('type') === 'keluar')],
        ];
    } elseif ($role === 'admin') {
        $menus = [
            ['title' => 'Dashboard', 'icon' => 'home', 'route' => 'admin.dashboard', 'active' => request()->routeIs('admin.dashboard')],
            ['title' => 'Kendaraan', 'icon' => 'car', 'route' => 'admin.kendaraan.index', 'active' => request()->routeIs('admin.kendaraan.*')],
            ['title' => 'Area Parkir', 'icon' => 'map-pin', 'route' => 'admin.area.index', 'active' => request()->routeIs('admin.area.*')],
            ['title' => 'Tarif Parkir', 'icon' => 'banknote', 'route' => 'admin.tarif.index', 'active' => request()->routeIs('admin.tarif.*')],
            ['title' => 'Manajemen User', 'icon' => 'users', 'route' => 'admin.users.index', 'active' => request()->routeIs('admin.users.*')],
            ['title' => 'Log Aktivitas', 'icon' => 'history', 'route' => 'admin.log.index', 'active' => request()->routeIs('admin.log.*')],
        ];
    } elseif ($role === 'owner') {
        $menus = [
            ['title' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'owner.dashboard', 'active' => request()->routeIs('owner.dashboard*')],
            ['title' => 'Rekap Transaksi', 'icon' => 'receipt', 'route' => 'owner.transaksi.index', 'active' => request()->routeIs('owner.transaksi.*')],
            ['title' => 'Laporan', 'icon' => 'file-bar-chart', 'route' => 'owner.laporan.index', 'active' => request()->routeIs('owner.laporan.*')],
        ];
    }
@endphp

<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transition-transform duration-300 lg:translate-x-0 flex flex-col">
    <!-- Header -->
    <div class="h-16 flex items-center px-6 border-b border-slate-200 dark:border-slate-800 shrink-0">
        <a href="/" class="flex items-center gap-2.5 font-bold text-xl text-slate-900 dark:text-white">
            <div class="bg-blue-600 text-white p-2 rounded-xl shadow-sm shadow-blue-500/30">
                <i data-lucide="car" class="w-5 h-5"></i>
            </div>
            <span>Smar<span class="text-blue-600">PARK</span></span>
        </a>
    </div>

    <!-- Quick Search trigger in sidebar -->
    <div class="p-4 shrink-0">
        <div class="relative cursor-pointer" @click="searchOpen = true">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
            <input type="text" readonly placeholder="Cari..."
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg pl-9 pr-12 py-2 text-sm text-slate-500 dark:text-slate-400 cursor-pointer focus:outline-none hover:border-slate-300 dark:hover:border-slate-600 transition-colors">
            <div
                class="absolute right-2 top-1/2 -translate-y-1/2 px-1.5 border border-slate-200 dark:border-slate-600 rounded text-[10px] font-medium text-slate-400 bg-white dark:bg-slate-700">
                Ctrl K</div>
        </div>
    </div>

    <!-- Menus -->
    <div class="flex-1 overflow-y-auto p-4 space-y-6 scrollbar-hide">
        <div>
            <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2.5 px-3">Menu Utama</div>
            <nav class="space-y-1">
                @foreach($menus as $menu)
                    <a href="{{ $menu['route'] !== '#' ? route($menu['route']) : '#' }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $menu['active'] ? 'bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <i data-lucide="{{ $menu['icon'] }}"
                            class="w-5 h-5 {{ $menu['active'] ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}"></i>
                        {{ $menu['title'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        @if($role !== 'owner')
        @php
            $profilRoute = $role === 'petugas' ? route('petugas.profil.index') : route('admin.profil.index');
            $profilActive = request()->routeIs('petugas.profil.*') || request()->routeIs('admin.profil.*');
            $pengaturanRoute = $role === 'petugas' ? route('petugas.pengaturan.index') : route('admin.pengaturan.index');
            $pengaturanActive = request()->routeIs('petugas.pengaturan.*') || request()->routeIs('admin.pengaturan.*');
        @endphp
        <div>
            <div class="text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2.5 px-3">Pengaturan & Akun</div>
            <nav class="space-y-1">
                <a href="{{ $profilRoute }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $profilActive ? 'bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                    <i data-lucide="user" class="w-5 h-5 {{ $profilActive ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}"></i> Profil Saya
                </a>
                <a href="{{ $pengaturanRoute }}"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $pengaturanActive ? 'bg-blue-50 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                    <i data-lucide="settings" class="w-5 h-5 {{ $pengaturanActive ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}"></i> Pengaturan Sistem
                </a>
            </nav>
        </div>
        @endif
    </div>

    <!-- User Info & Logout footer -->
    <div class="p-4 border-t border-slate-200 dark:border-slate-800 shrink-0">
        <div class="bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/80 rounded-xl p-3">
            <div class="flex items-center gap-3 mb-3">
                <div
                    class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm">
                    {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                </div>
                <div class="flex-1 overflow-hidden">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                        {{ $user->name ?? 'User' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 capitalize">{{ $role }}</p>
                </div>
            </div>
            <button type="button"
                @click="$dispatch('open-modal', 'modal-logout')"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors shadow-sm">
                <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
            </button>
        </div>
    </div>
</aside>