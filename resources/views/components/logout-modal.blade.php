<div
    x-data="{ show: false }"
    x-show="show"
    x-init="$watch('show', val => { 
        if (val) { 
            setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 50); 
        } 
    })"
    @open-modal.window="if ($event.detail === 'modal-logout' || $event.detail === 'logout') { show = true }"
    @open-logout-modal.window="show = true"
    @keydown.escape.window="show = false"
    style="display: none;"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 overflow-y-auto"
    x-cloak
    role="dialog"
    aria-modal="true"
    aria-labelledby="logout-modal-title"
>
    <!-- Background Backdrop / Overlay Gelap Transparan -->
    <div
        x-show="show"
        class="fixed inset-0 bg-slate-950/60 dark:bg-slate-950/80 backdrop-blur-xs transition-opacity"
        @click="show = false"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        aria-hidden="true"
    ></div>

    <!-- Modal Dialog Panel (Card di tengah layar) -->
    <div
        x-show="show"
        @click.stop
        class="relative z-10 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl w-full max-w-[420px] border border-slate-200/90 dark:border-slate-800 p-6 sm:p-7 text-center overflow-hidden"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
    >
        <!-- Tombol Close (X) di pojok kanan atas -->
        <button type="button" 
            @click="show = false" 
            aria-label="Tutup modal"
            class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>

        <!-- Ikon Peringatan Logout -->
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 ring-8 ring-red-50/60 dark:ring-red-950/20 mb-4 transition-transform">
            <i data-lucide="log-out" class="w-8 h-8"></i>
        </div>

        <!-- Judul & Deskripsi -->
        <h3 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight" id="logout-modal-title">
            Keluar dari Akun?
        </h3>
        
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
            Anda akan keluar dari sistem SmarPARK. Pastikan semua pekerjaan sudah tersimpan.
        </p>

        @if(auth()->check())
        <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800/70 border border-slate-200/60 dark:border-slate-700/60 text-xs text-slate-600 dark:text-slate-300">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Akun aktif: <strong class="text-slate-800 dark:text-slate-100">{{ auth()->user()->name }}</strong> ({{ ucfirst(auth()->user()->role ?? 'user') }})</span>
        </div>
        @endif

        <!-- Tombol Aksi (Batal & Ya, Keluar) -->
        <div class="mt-6 flex flex-col-reverse sm:flex-row gap-3">
            <button type="button"
                @click="show = false"
                class="w-full sm:w-1/2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/70 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300 dark:focus:ring-slate-600">
                Batal
            </button>

            <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-1/2 m-0 p-0">
                @csrf
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold shadow-lg shadow-red-600/25 hover:shadow-red-600/35 transition-all focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Ya, Keluar</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    if (typeof window.openLogoutModal === 'undefined') {
        window.openLogoutModal = function() {
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'modal-logout' }));
            window.dispatchEvent(new CustomEvent('open-logout-modal'));
        };
    }
</script>
