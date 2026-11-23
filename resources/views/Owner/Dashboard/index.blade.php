@extends('layouts.dashboard')

@section('title', 'Dashboard Owner')

@section('content')
<div x-data="ownerDashboard({
        initialRange: '{{ $rangeKey }}',
        startDate: '{{ $startDate->format('Y-m-d') }}',
        endDate: '{{ $endDate->format('Y-m-d') }}',
        startDateFormatted: '{{ $startDate->translatedFormat('d M Y') }}',
        endDateFormatted: '{{ $endDate->translatedFormat('d M Y') }}',
        stats: {{ json_encode($stats) }},
        chartPendapatanData: {{ json_encode($chartPendapatan) }},
        chartVolumeData: {{ json_encode($chartVolume) }},
        paymentMethods: {{ json_encode($paymentMethods) }},
        exportUrl: '{{ route('owner.dashboard.export') }}',
        ajaxDataUrl: '{{ route('owner.dashboard.data') }}'
    })" 
    x-init="initDashboard()"
    class="space-y-6">

    <!-- Topbar Banner / Page Header & Filter Rentang Waktu -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-xl">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Dashboard Owner</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Pemantauan performa, grafik tren, dan rekapitulasi operasional parkir SmarPARK.
                    </p>
                </div>
            </div>
        </div>

        <!-- Date Range Filter Group -->
        <div class="flex flex-wrap items-center gap-2 self-start lg:self-auto">
            <!-- Preset Pills -->
            <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200/70 dark:border-slate-700/60 text-xs font-medium">
                <button type="button" 
                    @click="setPresetRange('today')"
                    :class="selectedRange === 'today' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="px-3 py-1.5 rounded-lg transition-all duration-150">
                    Hari Ini
                </button>
                <button type="button" 
                    @click="setPresetRange('7_days')"
                    :class="selectedRange === '7_days' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="px-3 py-1.5 rounded-lg transition-all duration-150">
                    7 Hari Terakhir
                </button>
                <button type="button" 
                    @click="setPresetRange('this_month')"
                    :class="selectedRange === 'this_month' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="px-3 py-1.5 rounded-lg transition-all duration-150">
                    Bulan Ini
                </button>
                <button type="button" 
                    @click="customModalOpen = !customModalOpen"
                    :class="selectedRange === 'custom' ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-xs font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-all duration-150">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span x-text="selectedRange === 'custom' ? dateRangeLabel : 'Kustom'"></span>
                </button>
            </div>

            <!-- Tombol Export CSV Langsung -->
            <a :href="getExportUrl()" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs shadow-blue-500/20 transition-colors">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Modal / Popover Date Range Kustom -->
    <div x-show="customModalOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="customModalOpen = false" 
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-xl space-y-4 max-w-md w-full"
        x-cloak>
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
                <i data-lucide="calendar-range" class="w-4 h-4 text-blue-600"></i>
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Pilih Rentang Tanggal Kustom</h3>
            </div>
            <button type="button" @click="customModalOpen = false" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
            <div>
                <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Mulai</label>
                <input type="date" x-model="customStart" 
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block font-medium text-slate-700 dark:text-slate-300 mb-1">Tanggal Akhir</label>
                <input type="date" x-model="customEnd" 
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
            <button type="button" @click="customModalOpen = false" 
                class="px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">
                Batal
            </button>
            <button type="button" @click="applyCustomDate()" 
                class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm">
                Terapkan Filter
            </button>
        </div>
    </div>

    <!-- SKELETON / LOADING OVERLAY FOR CARDS & CHARTS -->
    <div x-show="loading" class="w-full flex items-center justify-center p-3 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-300 rounded-xl border border-blue-200 dark:border-blue-900 text-xs font-medium animate-pulse" x-cloak>
        <i data-lucide="loader-2" class="w-4 h-4 animate-spin mr-2"></i>
        <span>Memperbarui data statistik & grafik...</span>
    </div>

    <!-- 4 KARTU STATISTIK UTAMA (shadcn style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" :class="{ 'opacity-50 pointer-events-none transition-opacity': loading }">
        <!-- Kartu 1: Total Pendapatan -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:border-blue-300 dark:hover:border-blue-800 transition-all duration-200">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Pendapatan</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white" x-text="statsData.pendapatan.formatted">
                    {{ $stats['pendapatan']['formatted'] }}
                </h3>
            </div>
            <div class="mt-2.5 flex items-center gap-2 text-xs">
                <span :class="statsData.pendapatan.trend === 'up' ? 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50' : 'text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/50'"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold">
                    <i :data-lucide="statsData.pendapatan.trend === 'up' ? 'trending-up' : 'trending-down'" class="w-3 h-3"></i>
                    <span x-text="(statsData.pendapatan.change_pct >= 0 ? '+' : '') + statsData.pendapatan.change_pct + '%'">
                        {{ ($stats['pendapatan']['change_pct'] >= 0 ? '+' : '') . $stats['pendapatan']['change_pct'] }}%
                    </span>
                </span>
                <span class="text-slate-400 dark:text-slate-500 truncate">vs periode sebelumnya</span>
            </div>
        </div>

        <!-- Kartu 2: Total Transaksi -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:border-blue-300 dark:hover:border-blue-800 transition-all duration-200">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Transaksi</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    <span x-text="statsData.transaksi.formatted">{{ $stats['transaksi']['formatted'] }}</span>
                    <span class="text-sm font-medium text-slate-500 dark:text-slate-400 ml-1">Tiket</span>
                </h3>
            </div>
            <div class="mt-2.5 flex items-center gap-2 text-xs">
                <span :class="statsData.transaksi.trend === 'up' ? 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50' : 'text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/50'"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold">
                    <i :data-lucide="statsData.transaksi.trend === 'up' ? 'trending-up' : 'trending-down'" class="w-3 h-3"></i>
                    <span x-text="(statsData.transaksi.change_pct >= 0 ? '+' : '') + statsData.transaksi.change_pct + '%'">
                        {{ ($stats['transaksi']['change_pct'] >= 0 ? '+' : '') . $stats['transaksi']['change_pct'] }}%
                    </span>
                </span>
                <span class="text-slate-400 dark:text-slate-500 truncate">vs periode sebelumnya</span>
            </div>
        </div>

        <!-- Kartu 3: Kendaraan Masuk Hari Ini -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:border-blue-300 dark:hover:border-blue-800 transition-all duration-200">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kendaraan Hari Ini</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                    <i data-lucide="car" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    <span x-text="statsData.kendaraan_hari_ini.formatted">{{ $stats['kendaraan_hari_ini']['formatted'] }}</span>
                    <span class="text-sm font-medium text-slate-500 dark:text-slate-400 ml-1">Unit</span>
                </h3>
            </div>
            <div class="mt-2.5 flex items-center gap-2 text-xs">
                <span :class="statsData.kendaraan_hari_ini.trend === 'up' ? 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/50' : 'text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/50'"
                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold">
                    <i :data-lucide="statsData.kendaraan_hari_ini.trend === 'up' ? 'arrow-up' : 'arrow-down'" class="w-3 h-3"></i>
                    <span x-text="(statsData.kendaraan_hari_ini.change_pct >= 0 ? '+' : '') + statsData.kendaraan_hari_ini.change_pct + '%'">
                        {{ ($stats['kendaraan_hari_ini']['change_pct'] >= 0 ? '+' : '') . $stats['kendaraan_hari_ini']['change_pct'] }}%
                    </span>
                </span>
                <span class="text-slate-400 dark:text-slate-500 truncate" x-text="'vs ' + statsData.kendaraan_hari_ini.prev_formatted">
                    vs {{ $stats['kendaraan_hari_ini']['prev_formatted'] }}
                </span>
            </div>
        </div>

        <!-- Kartu 4: Rata-rata Durasi Parkir -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs hover:border-blue-300 dark:hover:border-blue-800 transition-all duration-200">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rata-rata Durasi</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="clock" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white" x-text="statsData.avg_durasi.formatted">
                    {{ $stats['avg_durasi']['formatted'] }}
                </h3>
            </div>
            <div class="mt-2.5 flex items-center gap-2 text-xs">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                    <i data-lucide="activity" class="w-3 h-3 text-blue-500"></i>
                    <span x-text="statsData.avg_durasi.raw_minutes + ' menit'">{{ $stats['avg_durasi']['raw_minutes'] }} menit</span>
                </span>
                <span class="text-slate-400 dark:text-slate-500 truncate">per kendaraan selesai</span>
            </div>
        </div>
    </div>

    <!-- ROW 1: GRAFIK PENDAPATAN (LINE) & DISTRIBUSI PEMBAYARAN -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Grafik Line: Tren Pendapatan -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 dark:border-slate-800 gap-2">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="line-chart" class="w-4 h-4 text-blue-600"></i>
                        Tren Pendapatan Parkir
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Akumulasi pembayaran parkir selesai pada rentang waktu yang dipilih
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200/60 dark:border-blue-900/60" 
                        x-text="'Total: ' + chartPendapatanState.total_formatted">
                        Total: {{ $chartPendapatan['total_formatted'] }}
                    </span>
                </div>
            </div>

            <!-- Canvas Chart.js -->
            <div class="relative w-full h-72 sm:h-80 mt-4">
                <canvas id="ownerRevenueChart"></canvas>
            </div>
        </div>

        <!-- Distribusi Metode Pembayaran (Card shadcn style) -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="credit-card" class="w-4 h-4 text-cyan-600"></i>
                            Metode Pembayaran
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Proporsi transaksi tunai vs non-tunai</p>
                    </div>
                </div>

                <!-- Doughnut Canvas -->
                <div class="relative w-full h-44 my-3 flex items-center justify-center">
                    <canvas id="paymentMethodChart"></canvas>
                </div>

                <!-- Breakdown List -->
                <div class="space-y-3 pt-2">
                    <!-- Tunai -->
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-200">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                <span>Tunai (Cash)</span>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white" x-text="paymentState.tunai.pct + '%'">
                                {{ $paymentMethods['tunai']['pct'] }}%
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                            <span x-text="paymentState.tunai.count + ' transaksi'">{{ $paymentMethods['tunai']['count'] }} transaksi</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300" x-text="paymentState.tunai.nominal_formatted">
                                {{ $paymentMethods['tunai']['nominal_formatted'] }}
                            </span>
                        </div>
                    </div>

                    <!-- QRIS -->
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-200">
                                <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                                <span>QRIS Digital</span>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white" x-text="paymentState.qris.pct + '%'">
                                {{ $paymentMethods['qris']['pct'] }}%
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                            <span x-text="paymentState.qris.count + ' transaksi'">{{ $paymentMethods['qris']['count'] }} transaksi</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300" x-text="paymentState.qris.nominal_formatted">
                                {{ $paymentMethods['qris']['nominal_formatted'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Transfer / Non-Tunai Lainnya -->
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 font-semibold text-slate-800 dark:text-slate-200">
                                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                <span>Transfer / Lainnya</span>
                            </div>
                            <span class="font-bold text-slate-900 dark:text-white" x-text="paymentState.transfer.pct + '%'">
                                {{ $paymentMethods['transfer']['pct'] }}%
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                            <span x-text="paymentState.transfer.count + ' transaksi'">{{ $paymentMethods['transfer']['count'] }} transaksi</span>
                            <span class="font-medium text-slate-700 dark:text-slate-300" x-text="paymentState.transfer.nominal_formatted">
                                {{ $paymentMethods['transfer']['nominal_formatted'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer summary -->
            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                <span>Total Omset Selesai:</span>
                <span class="font-bold text-slate-900 dark:text-white" x-text="paymentState.total_nominal_formatted">
                    {{ $paymentMethods['total_nominal_formatted'] }}
                </span>
            </div>
        </div>
    </div>

    <!-- ROW 2: GRAFIK VOLUME KENDARAAN (BAR) & HIGHLIGHTS -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Grafik Bar: Volume Kendaraan Masuk -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-slate-100 dark:border-slate-800 gap-2">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 text-indigo-600"></i>
                        Volume Kendaraan Masuk
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Jumlah unit kendaraan yang masuk berdasarkan kategori (Mobil, Motor, Truk/Bus)
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1.5 font-medium text-slate-600 dark:text-slate-400">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> Mobil
                    </span>
                    <span class="flex items-center gap-1.5 font-medium text-slate-600 dark:text-slate-400">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span> Motor
                    </span>
                    <span class="flex items-center gap-1.5 font-medium text-slate-600 dark:text-slate-400">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Lainnya
                    </span>
                </div>
            </div>

            <!-- Canvas Bar Chart -->
            <div class="relative w-full h-72 sm:h-80 mt-4">
                <canvas id="ownerVolumeChart"></canvas>
            </div>
        </div>

        <!-- Highlight & Analisis Ringkas Owner -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 sm:p-6 shadow-xs flex flex-col justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
                    Highlight & Catatan
                </h2>

                <div class="mt-4 space-y-4">
                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50">
                        <div class="p-2 rounded-lg bg-blue-600 text-white shrink-0 mt-0.5">
                            <i data-lucide="badge-percent" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-blue-950 dark:text-blue-200">Akurasi Transaksi</h4>
                            <p class="text-xs text-blue-800 dark:text-blue-300/80 mt-0.5">
                                Seluruh tiket parkir tersinkronisasi realtime dengan pencatatan nama petugas pada gerbang keluar.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 rounded-xl bg-cyan-50/70 dark:bg-cyan-950/30 border border-cyan-100 dark:border-cyan-900/50">
                        <div class="p-2 rounded-lg bg-cyan-600 text-white shrink-0 mt-0.5">
                            <i data-lucide="qr-code" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-cyan-950 dark:text-cyan-200">Adopsi Non-Tunai</h4>
                            <p class="text-xs text-cyan-800 dark:text-cyan-300/80 mt-0.5">
                                Pembayaran digital (QRIS & Transfer) memudahkan rekapitulasi tanpa selisih kas fisik.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50">
                        <div class="p-2 rounded-lg bg-emerald-600 text-white shrink-0 mt-0.5">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-emerald-950 dark:text-emerald-200">Export Laporan</h4>
                            <p class="text-xs text-emerald-800 dark:text-emerald-300/80 mt-0.5">
                                Rekap CSV dapat dibuka langsung di Microsoft Excel dengan format UTF-8 rapi siap audit.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400">
                Mode Akses: <strong class="text-slate-700 dark:text-slate-300">Owner (Hak Pantau & Rekap)</strong>
            </div>
        </div>
    </div>

    <!-- TABEL REKAP TRANSAKSI (Lengkap dengan Filter, Search, Pagination, dan Export) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <!-- Table Header & Controls Bar -->
        <div class="p-5 sm:p-6 border-b border-slate-200 dark:border-slate-800">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="table" class="w-4 h-4 text-blue-600"></i>
                        Rekap Transaksi Parkir
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Daftar rincian kendaraan pada periode <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="dateRangeLabel"></span>
                    </p>
                </div>

                <!-- Form Filter & Pencarian -->
                <form method="GET" action="{{ route('owner.dashboard') }}" class="flex flex-wrap items-center gap-2.5">
                    <input type="hidden" name="range" :value="selectedRange">
                    <input type="hidden" name="start_date" :value="customStart">
                    <input type="hidden" name="end_date" :value="customEnd">

                    <!-- Search Input -->
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari plat / kode tiket..."
                            class="pl-9 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 w-44 sm:w-56">
                    </div>

                    <!-- Status Filter -->
                    <select name="status" onchange="this.form.submit()"
                        class="px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="parkir" {{ request('status') == 'parkir' ? 'selected' : '' }}>Masih Parkir</option>
                        <option value="batal" {{ request('status') == 'batal' ? 'selected' : '' }}>Batal</option>
                    </select>

                    <!-- Metode Bayar Filter -->
                    <select name="metode_bayar" onchange="this.form.submit()"
                        class="px-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all" {{ request('metode_bayar') == 'all' ? 'selected' : '' }}>Semua Metode</option>
                        <option value="Tunai" {{ request('metode_bayar') == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                        <option value="QRIS" {{ request('metode_bayar') == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                        <option value="Transfer" {{ request('metode_bayar') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>

                    <button type="submit" 
                        class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                        Filter
                    </button>

                    <!-- Tombol Export CSV Sesuai Filter -->
                    <a :href="getExportUrl('{{ request('q') }}', '{{ request('status') }}', '{{ request('metode_bayar') }}')" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-950/60 hover:bg-blue-100 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-900 text-xs font-semibold rounded-xl transition-colors">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>CSV</span>
                    </a>
                </form>
            </div>
        </div>

        <!-- Tabel Transaksi -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">Kendaraan & Tiket</th>
                        <th class="px-4 py-3.5">Area</th>
                        <th class="px-4 py-3.5">Waktu Masuk</th>
                        <th class="px-4 py-3.5">Waktu Keluar</th>
                        <th class="px-4 py-3.5">Durasi</th>
                        <th class="px-4 py-3.5">Tarif Total</th>
                        <th class="px-4 py-3.5">Metode</th>
                        <th class="px-4 py-3.5">Petugas</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($transaksis as $t)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <!-- Plat Nomor & Kode Tiket -->
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 {{ $t->jenis_kendaraan === 'Mobil' ? 'bg-blue-50 dark:bg-blue-950 text-blue-600' : ($t->jenis_kendaraan === 'Motor' ? 'bg-cyan-50 dark:bg-cyan-950 text-cyan-600' : 'bg-amber-50 dark:bg-amber-950 text-amber-600') }}">
                                    <i data-lucide="{{ $t->jenis_kendaraan === 'Mobil' ? 'car' : ($t->jenis_kendaraan === 'Motor' ? 'bike' : 'truck') }}" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $t->plat_nomor }}</span>
                                    <span class="text-[11px] font-mono text-slate-400">{{ $t->kode_tiket }} &bull; {{ $t->jenis_kendaraan }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Area Parkir -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="text-xs text-slate-600 dark:text-slate-300 font-medium">
                                {{ $t->areaParkir ? $t->areaParkir->nama_area : '-' }}
                            </span>
                        </td>

                        <!-- Waktu Masuk -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="block font-medium text-slate-800 dark:text-slate-200">
                                {{ $t->waktu_masuk ? $t->waktu_masuk->translatedFormat('d M Y') : '-' }}
                            </span>
                            <span class="text-[11px] text-slate-400">
                                {{ $t->waktu_masuk ? $t->waktu_masuk->format('H:i') . ' WIB' : '-' }}
                            </span>
                        </td>

                        <!-- Waktu Keluar -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if($t->waktu_keluar)
                                <span class="block font-medium text-slate-800 dark:text-slate-200">
                                    {{ $t->waktu_keluar->translatedFormat('d M Y') }}
                                </span>
                                <span class="text-[11px] text-slate-400">
                                    {{ $t->waktu_keluar->format('H:i') . ' WIB' }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400 italic">Sedang parkir</span>
                            @endif
                        </td>

                        <!-- Durasi -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if($t->durasi_menit !== null && $t->durasi_menit > 0)
                                @php
                                    $h = floor($t->durasi_menit / 60);
                                    $m = $t->durasi_menit % 60;
                                @endphp
                                <span class="font-medium text-slate-800 dark:text-slate-200">
                                    {{ $h > 0 ? "{$h} jam " : '' }}{{ $m }} mnt
                                </span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <!-- Tarif Total -->
                        <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-900 dark:text-white">
                            @if($t->total_bayar)
                                Rp {{ number_format($t->total_bayar, 0, ',', '.') }}
                            @else
                                <span class="text-xs font-normal text-slate-400 italic">Belum checkout</span>
                            @endif
                        </td>

                        <!-- Metode Bayar -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if($t->metode_bayar)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md font-semibold text-[11px] {{ strtolower($t->metode_bayar) === 'qris' ? 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                    {{ $t->metode_bayar }}
                                </span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>

                        <!-- Petugas -->
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="text-xs text-slate-700 dark:text-slate-300">
                                {{ $t->petugas ? $t->petugas->name : '-' }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td class="px-5 py-3.5 whitespace-nowrap text-center">
                            @if($t->status === 'selesai')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Selesai
                                </span>
                            @elseif($t->status === 'parkir')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    Parkir
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200/60 dark:border-rose-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Batal
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="inbox" class="w-10 h-10 mb-2 stroke-1 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">Tidak ada transaksi ditemukan</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau rentang tanggal yang dipilih.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        @if($transaksis->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $transaksis->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
function ownerDashboard(config) {
    return {
        selectedRange: config.initialRange || '7_days',
        customStart: config.startDate,
        customEnd: config.endDate,
        dateRangeLabel: config.startDateFormatted + ' - ' + config.endDateFormatted,
        customModalOpen: false,
        loading: false,

        statsData: config.stats,
        chartPendapatanState: config.chartPendapatanData,
        chartVolumeState: config.chartVolumeData,
        paymentState: config.paymentMethods,

        revenueChartInstance: null,
        volumeChartInstance: null,
        paymentChartInstance: null,

        initDashboard() {
            this.$nextTick(() => {
                this.renderRevenueChart();
                this.renderVolumeChart();
                this.renderPaymentChart();
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            });
        },

        getExportUrl(search = '', status = '', payment = '') {
            let url = config.exportUrl + '?range=' + encodeURIComponent(this.selectedRange);
            if (this.selectedRange === 'custom') {
                url += '&start_date=' + encodeURIComponent(this.customStart) + '&end_date=' + encodeURIComponent(this.customEnd);
            }
            if (search) url += '&q=' + encodeURIComponent(search);
            if (status && status !== 'all') url += '&status=' + encodeURIComponent(status);
            if (payment && payment !== 'all') url += '&metode_bayar=' + encodeURIComponent(payment);
            return url;
        },

        async setPresetRange(preset) {
            if (this.selectedRange === preset && !this.loading) return;
            this.selectedRange = preset;
            await this.fetchData();
        },

        async applyCustomDate() {
            if (!this.customStart || !this.customEnd) {
                alert('Pilih tanggal awal dan akhir!');
                return;
            }
            this.selectedRange = 'custom';
            this.customModalOpen = false;
            await this.fetchData();
        },

        async fetchData() {
            this.loading = true;
            try {
                let url = config.ajaxDataUrl + '?range=' + encodeURIComponent(this.selectedRange);
                if (this.selectedRange === 'custom') {
                    url += '&start_date=' + encodeURIComponent(this.customStart) + '&end_date=' + encodeURIComponent(this.customEnd);
                }

                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!res.ok) throw new Error('Gagal mengambil data');
                const json = await res.json();

                if (json.success) {
                    this.statsData = json.stats;
                    this.chartPendapatanState = json.chart_pendapatan;
                    this.chartVolumeState = json.chart_volume;
                    this.paymentState = json.payment_methods;
                    this.dateRangeLabel = json.start_date_formatted + ' - ' + json.end_date_formatted;

                    // Update Charts
                    this.updateCharts();
                }
            } catch (err) {
                console.error('Error fetching dashboard data:', err);
                // Fallback: Reload via GET query
                window.location.href = '?range=' + this.selectedRange + 
                    (this.selectedRange === 'custom' ? '&start_date=' + this.customStart + '&end_date=' + this.customEnd : '');
            } finally {
                this.loading = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        },

        renderRevenueChart() {
            const ctx = document.getElementById('ownerRevenueChart');
            if (!ctx) return;

            // Gradient background #2563EB to transparent
            const chartCtx = ctx.getContext('2d');
            const gradient = chartCtx.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.28)');
            gradient.addColorStop(1, 'rgba(37, 99, 235, 0.00)');

            this.revenueChartInstance = new Chart(chartCtx, {
                type: 'line',
                data: {
                    labels: this.chartPendapatanState.labels,
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: this.chartPendapatanState.data,
                        borderColor: '#2563EB',
                        backgroundColor: gradient,
                        borderWidth: 2.5,
                        pointBackgroundColor: '#2563EB',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#1e293b' : '#f1f5f9',
                                borderDash: [3, 4]
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 },
                                callback: function(val) {
                                    if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + ' jt';
                                    if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + ' rb';
                                    return 'Rp ' + val;
                                }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            bodyColor: '#93c5fd',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' Total: Rp ' + Number(context.raw).toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        },

        renderVolumeChart() {
            const ctx = document.getElementById('ownerVolumeChart');
            if (!ctx) return;

            this.volumeChartInstance = new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: this.chartVolumeState.labels,
                    datasets: [
                        {
                            label: 'Mobil',
                            data: this.chartVolumeState.mobil,
                            backgroundColor: '#2563EB',
                            borderRadius: 6,
                            stack: 'combined'
                        },
                        {
                            label: 'Motor',
                            data: this.chartVolumeState.motor,
                            backgroundColor: '#06B6D4',
                            borderRadius: 6,
                            stack: 'combined'
                        },
                        {
                            label: 'Lainnya',
                            data: this.chartVolumeState.lainnya,
                            backgroundColor: '#6366F1',
                            borderRadius: 6,
                            stack: 'combined'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { 
                                precision: 0,
                                color: '#94a3b8',
                                font: { size: 11 }
                            },
                            grid: {
                                color: document.documentElement.classList.contains('dark') ? '#1e293b' : '#f1f5f9',
                                borderDash: [3, 4]
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 11 }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleColor: '#f8fafc',
                            padding: 10,
                            cornerRadius: 8,
                        }
                    }
                }
            });
        },

        renderPaymentChart() {
            const ctx = document.getElementById('paymentMethodChart');
            if (!ctx) return;

            const t = this.paymentState.tunai.count;
            const q = this.paymentState.qris.count;
            const tr = this.paymentState.transfer.count;

            this.paymentChartInstance = new Chart(ctx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Tunai', 'QRIS', 'Transfer/Lainnya'],
                    datasets: [{
                        data: [t, q, tr],
                        backgroundColor: ['#2563EB', '#06B6D4', '#6366F1'],
                        borderWidth: 2,
                        borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + context.raw + ' transaksi';
                                }
                            }
                        }
                    }
                }
            });
        },

        updateCharts() {
            // 1. Update Revenue Chart
            if (this.revenueChartInstance) {
                this.revenueChartInstance.data.labels = this.chartPendapatanState.labels;
                this.revenueChartInstance.data.datasets[0].data = this.chartPendapatanState.data;
                this.revenueChartInstance.update();
            }

            // 2. Update Volume Chart
            if (this.volumeChartInstance) {
                this.volumeChartInstance.data.labels = this.chartVolumeState.labels;
                this.volumeChartInstance.data.datasets[0].data = this.chartVolumeState.mobil;
                this.volumeChartInstance.data.datasets[1].data = this.chartVolumeState.motor;
                this.volumeChartInstance.data.datasets[2].data = this.chartVolumeState.lainnya;
                this.volumeChartInstance.update();
            }

            // 3. Update Payment Chart
            if (this.paymentChartInstance) {
                this.paymentChartInstance.data.datasets[0].data = [
                    this.paymentState.tunai.count,
                    this.paymentState.qris.count,
                    this.paymentState.transfer.count
                ];
                this.paymentChartInstance.update();
            }
        }
    };
}
</script>
@endpush
