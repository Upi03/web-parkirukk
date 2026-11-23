<header
    class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-x-4 border-b border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md px-4 sm:gap-x-6 sm:px-6 lg:px-8">

    <!-- Mobile sidebar toggle -->
    <button type="button" class="-m-2.5 p-2.5 text-slate-700 dark:text-slate-200 lg:hidden" @click="sidebarOpen = true">
        <span class="sr-only">Buka sidebar</span>
        <i data-lucide="menu" class="h-6 w-6"></i>
    </button>

    <!-- Separator -->
    <div class="h-6 w-px bg-slate-200 dark:bg-slate-800 lg:hidden" aria-hidden="true"></div>

    <div class="flex flex-1 items-center justify-between gap-x-4 self-stretch lg:gap-x-6">

        <!-- Breadcrumb / Page Title -->
        <div class="flex items-center text-sm font-medium text-slate-500 dark:text-slate-400">
            <span class="capitalize">{{ auth()->user()->role ?? 'Admin' }}</span>
            <i data-lucide="chevron-right" class="h-4 w-4 mx-1"></i>
            <span class="text-slate-900 dark:text-white font-semibold">@yield('title', 'SmarPARK')</span>
        </div>

        <div class="flex items-center gap-x-4 lg:gap-x-6">

            <!-- Global Search Trigger Button (Desktop) -->
            <button type="button" @click="searchOpen = true" 
                class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 text-xs rounded-lg border border-slate-200 dark:border-slate-700 transition-colors">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                <span>Cari cepat...</span>
                <kbd class="ml-2 px-1.5 py-0.5 rounded bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-[10px]">Ctrl K</kbd>
            </button>

            <!-- Dark Mode Toggle -->
            <button type="button" @click="darkMode = !darkMode"
                class="-m-2.5 p-2.5 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition-colors"
                aria-label="Toggle Dark Mode"
                :title="darkMode ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'">
                <i data-lucide="sun" class="h-5 w-5" x-show="darkMode" x-cloak></i>
                <i data-lucide="moon" class="h-5 w-5" x-show="!darkMode"></i>
            </button>

            <!-- Notification Dropdown -->
            <div class="relative" x-data="{ notifOpen: false }">
                <button type="button" @click="notifOpen = !notifOpen" @click.outside="notifOpen = false"
                    class="relative -m-2.5 p-2.5 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition-colors"
                    aria-label="Notifikasi Sistem">
                    <span class="sr-only">Notifikasi</span>
                    <i data-lucide="bell" class="h-5 w-5"></i>
                    
                    @if(!empty($systemNotifications) && count($systemNotifications) > 0)
                        <span class="absolute top-2 right-2 h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-white dark:ring-slate-900 animate-pulse"></span>
                    @endif
                </button>

                <!-- Notifications Dropdown Panel -->
                <div x-show="notifOpen" x-transition.origin.top.right
                    class="absolute right-0 z-40 mt-3 w-80 sm:w-96 origin-top-right rounded-xl bg-white dark:bg-slate-800 p-2 shadow-2xl ring-1 ring-slate-900/10 dark:ring-white/10 focus:outline-none border border-slate-200 dark:border-slate-700"
                    x-cloak>
                    <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Pemberitahuan Sistem</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ !empty($systemNotifications) ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : 'bg-slate-100 text-slate-600' }}">
                            {{ !empty($systemNotifications) ? count($systemNotifications) . ' Peringatan' : '0 Baru' }}
                        </span>
                    </div>

                    <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50 py-1">
                        @forelse($systemNotifications ?? [] as $notif)
                        <a href="{{ $notif['url'] }}" @click="notifOpen = false" 
                            class="flex items-start gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-lg transition-colors group">
                            <div class="mt-0.5 p-1.5 rounded-lg shrink-0 {{ $notif['type'] === 'danger' ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' : ($notif['type'] === 'warning' ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400') }}">
                                <i data-lucide="{{ $notif['icon'] }}" class="w-4 h-4"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 group-hover:text-blue-600">{{ $notif['title'] }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2 leading-relaxed">{{ $notif['message'] }}</p>
                            </div>
                        </a>
                        @empty
                        <div class="py-6 text-center text-slate-500 dark:text-slate-400">
                            <i data-lucide="check-circle-2" class="w-8 h-8 mx-auto mb-2 text-emerald-500 opacity-60"></i>
                            <p class="text-xs">Tidak ada peringatan.</p>
                            <p class="text-[11px] text-slate-400">Kapasitas area dan sistem berjalan normal.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Separator -->
            <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-slate-200 dark:lg:bg-slate-800" aria-hidden="true"></div>

            <!-- Profile dropdown -->
            <div class="relative" x-data="{ profileOpen: false }">
                <button type="button" class="-m-1.5 flex items-center p-1.5 focus:outline-none"
                    @click="profileOpen = !profileOpen" @click.outside="profileOpen = false">
                    <span class="sr-only">Menu pengguna</span>
                    <div
                        class="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <span class="hidden lg:flex lg:items-center">
                        <span class="ml-3 text-sm font-semibold leading-6 text-slate-900 dark:text-white"
                            aria-hidden="true">{{ auth()->user()->name ?? 'Admin' }}</span>
                        <i data-lucide="chevron-down" class="ml-2 h-4 w-4 text-slate-400"></i>
                    </span>
                </button>

                <!-- Dropdown menu -->
                <div x-show="profileOpen" x-transition.origin.top.right
                    class="absolute right-0 z-40 mt-2.5 w-52 origin-top-right rounded-xl bg-white dark:bg-slate-800 py-2 shadow-xl ring-1 ring-slate-900/5 dark:ring-white/10 focus:outline-none border border-slate-100 dark:border-slate-700"
                    x-cloak>
                    <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ auth()->user()->email ?? 'email@example.com' }}</p>
                        <span class="mt-1 inline-block text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300">
                            {{ auth()->user()->role ?? 'Admin' }}
                        </span>
                    </div>
                    
                    @if(auth()->user() && auth()->user()->role !== 'owner')
                    @php
                        $topbarProfilUrl = auth()->user()->role === 'petugas' ? route('petugas.profil.index') : route('admin.profil.index');
                        $topbarPengaturanUrl = auth()->user()->role === 'petugas' ? route('petugas.pengaturan.index') : route('admin.pengaturan.index');
                    @endphp

                    <a href="{{ $topbarProfilUrl }}"
                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <i data-lucide="user" class="w-4 h-4 text-slate-400"></i> Profil Saya
                    </a>

                    <a href="{{ $topbarPengaturanUrl }}"
                        class="flex items-center gap-2.5 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <i data-lucide="settings" class="w-4 h-4 text-slate-400"></i> Pengaturan
                    </a>
                    @endif

                    <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>

                    <button type="button"
                        @click="profileOpen = false; $dispatch('open-modal', 'modal-logout')"
                        class="flex items-center gap-2.5 w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i> Keluar
                    </button>
                </div>
            </div>

        </div>
    </div>
</header>