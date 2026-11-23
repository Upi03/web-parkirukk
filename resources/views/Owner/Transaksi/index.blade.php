@extends('layouts.dashboard')

@section('title', 'Rekap Transaksi Parkir - Owner')

@section('content')
<div x-data="rekapTransaksiPage()" class="space-y-6">

    <!-- Top Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-xs">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-xl">
                <i data-lucide="receipt" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Rekapitulasi Transaksi</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Audit log dan pemantauan riwayat tiket parkir SmarPARK.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('owner.dashboard.export', request()->query()) }}" 
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs shadow-blue-500/20 transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Export CSV Hasil Filter</span>
            </a>
        </div>
    </div>

    <!-- 4 KPI Cards Hasil Filter -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Transaksi -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Total Tiket Terpilih</span>
                <i data-lucide="ticket" class="w-4 h-4 text-blue-600"></i>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-2">{{ number_format($totalCount, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Sesuai kriteria filter aktif</p>
        </div>

        <!-- Total Pendapatan Selesai -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Omset Transaksi Selesai</span>
                <i data-lucide="wallet" class="w-4 h-4 text-emerald-600"></i>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-2">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5">Dari transaksi status selesai</p>
        </div>

        <!-- Kendaraan Masih Parkir -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Kendaraan Aktif</span>
                <i data-lucide="car" class="w-4 h-4 text-cyan-600"></i>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-2">{{ number_format($totalParkirAktif, 0, ',', '.') }}</p>
            <p class="text-[11px] text-cyan-600 dark:text-cyan-400 mt-0.5">Masih berada di area parkir</p>
        </div>

        <!-- Non-Tunai / QRIS -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 shadow-xs">
            <div class="flex items-center justify-between text-slate-500 text-xs font-medium">
                <span>Transaksi QRIS / Non-Tunai</span>
                <i data-lucide="qr-code" class="w-4 h-4 text-indigo-600"></i>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white mt-2">{{ number_format($totalQris, 0, ',', '.') }}</p>
            <p class="text-[11px] text-indigo-600 dark:text-indigo-400 mt-0.5">Pembayaran cashless</p>
        </div>
    </div>

    <!-- Filter Card Box -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
        <form method="GET" action="{{ route('owner.transaksi.index') }}" class="space-y-4">
            <!-- Row 1: Presets & Search -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex flex-wrap items-center gap-1.5">
                    <span class="text-xs font-semibold text-slate-500 mr-1">Preset Waktu:</span>
                    @php $currentRange = request('range', '30_days'); @endphp
                    <a href="{{ route('owner.transaksi.index', array_merge(request()->except(['range', 'start_date', 'end_date', 'page']), ['range' => 'today'])) }}"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors {{ $currentRange === 'today' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                        Hari Ini
                    </a>
                    <a href="{{ route('owner.transaksi.index', array_merge(request()->except(['range', 'start_date', 'end_date', 'page']), ['range' => '7_days'])) }}"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors {{ $currentRange === '7_days' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                        7 Hari
                    </a>
                    <a href="{{ route('owner.transaksi.index', array_merge(request()->except(['range', 'start_date', 'end_date', 'page']), ['range' => '30_days'])) }}"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors {{ $currentRange === '30_days' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                        30 Hari
                    </a>
                    <a href="{{ route('owner.transaksi.index', array_merge(request()->except(['range', 'start_date', 'end_date', 'page']), ['range' => 'this_month'])) }}"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors {{ $currentRange === 'this_month' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                        Bulan Ini
                    </a>
                    <a href="{{ route('owner.transaksi.index', array_merge(request()->except(['range', 'start_date', 'end_date', 'page']), ['range' => 'all'])) }}"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium transition-colors {{ $currentRange === 'all' ? 'bg-blue-600 text-white font-semibold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200' }}">
                        Semua Periode
                    </a>
                </div>

                <!-- Input Pencarian -->
                <div class="relative w-full lg:w-72">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari plat / kode tiket..."
                        class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Row 2: Filter Multikolom Dropdown -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
                <!-- Start Date -->
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Jenis Kendaraan -->
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Jenis Kendaraan</label>
                    <select name="jenis_kendaraan"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Semua Jenis</option>
                        <option value="Mobil" {{ request('jenis_kendaraan') === 'Mobil' ? 'selected' : '' }}>Mobil</option>
                        <option value="Motor" {{ request('jenis_kendaraan') === 'Motor' ? 'selected' : '' }}>Motor</option>
                        <option value="Truk / Bus" {{ request('jenis_kendaraan') === 'Truk / Bus' ? 'selected' : '' }}>Truk / Bus</option>
                    </select>
                </div>

                <!-- Area Parkir -->
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Area Parkir</label>
                    <select name="area_id"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Semua Area</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->nama_area }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status -->
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Status Tiket</label>
                    <select name="status"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Semua Status</option>
                        <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="parkir" {{ request('status') === 'parkir' ? 'selected' : '' }}>Sedang Parkir</option>
                        <option value="batal" {{ request('status') === 'batal' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>

                <!-- Metode Bayar -->
                <div>
                    <label class="block font-medium text-slate-600 dark:text-slate-400 mb-1">Metode Bayar</label>
                    <select name="metode_bayar"
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-2.5 py-1.5 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all">Semua Metode</option>
                        <option value="Tunai" {{ request('metode_bayar') === 'Tunai' ? 'selected' : '' }}>Tunai (Cash)</option>
                        <option value="QRIS" {{ request('metode_bayar') === 'QRIS' ? 'selected' : '' }}>QRIS</option>
                        <option value="Transfer" {{ request('metode_bayar') === 'Transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('owner.transaksi.index') }}" 
                    class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                    Reset Filter
                </a>
                <button type="submit" 
                    class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs shadow-blue-500/20 transition-colors">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Hasil Rekap Transaksi -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50/80 dark:bg-slate-800/50 border-b border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">Kendaraan & Tiket</th>
                        <th class="px-4 py-3.5">Area Parkir</th>
                        <th class="px-4 py-3.5">Waktu Masuk</th>
                        <th class="px-4 py-3.5">Waktu Keluar</th>
                        <th class="px-4 py-3.5">Durasi</th>
                        <th class="px-4 py-3.5">Tarif Total</th>
                        <th class="px-4 py-3.5">Metode</th>
                        <th class="px-4 py-3.5">Petugas</th>
                        <th class="px-4 py-3.5 text-center">Status</th>
                        <th class="px-4 py-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($transaksis as $t)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                        <!-- Plat Nomor & Kode Tiket -->
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $t->jenis_kendaraan === 'Mobil' ? 'bg-blue-50 dark:bg-blue-950 text-blue-600' : ($t->jenis_kendaraan === 'Motor' ? 'bg-cyan-50 dark:bg-cyan-950 text-cyan-600' : 'bg-amber-50 dark:bg-amber-950 text-amber-600') }}">
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
                            <span class="text-xs text-slate-700 dark:text-slate-300 font-medium">
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
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md font-semibold text-[11px] {{ strtolower($t->metode_bayar) === 'qris' ? 'bg-cyan-50 dark:bg-cyan-950/60 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
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
                        <td class="px-4 py-3.5 whitespace-nowrap text-center">
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

                        <!-- Aksi Detail -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-center">
                            <button type="button" @click="showDetail({{ $t->id }})"
                                class="p-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 transition-colors"
                                title="Lihat Rincian Tiket">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i data-lucide="inbox" class="w-10 h-10 mb-2 stroke-1 text-slate-300 dark:text-slate-600"></i>
                                <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">Tidak ada riwayat transaksi</p>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter tanggal atau parameter pencarian di atas.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transaksis->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $transaksis->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL DETAIL TRANSAKSI PARKIR -->
    <div x-show="modalOpen" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4"
        x-cloak>
        <div @click.outside="modalOpen = false"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 bg-blue-50 dark:bg-blue-950 text-blue-600 rounded-xl">
                        <i data-lucide="receipt" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Rincian Tiket Parkir</h3>
                        <p class="text-xs text-slate-400 font-mono" x-text="detail.kode_tiket"></p>
                    </div>
                </div>
                <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Content (Loading & Detail) -->
            <template x-if="modalLoading">
                <div class="py-12 flex flex-col items-center justify-center text-slate-400 text-xs">
                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin mb-2 text-blue-600"></i>
                    <span>Memuat informasi transaksi...</span>
                </div>
            </template>

            <template x-if="!modalLoading && detail.id">
                <div class="space-y-4 text-xs">
                    <!-- Info Kendaraan Banner -->
                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Nomor Polisi</span>
                            <h4 class="text-lg font-black text-slate-900 dark:text-white" x-text="detail.plat_nomor"></h4>
                            <span class="text-xs text-blue-600 font-medium" x-text="detail.jenis_kendaraan + ' &bull; ' + detail.area_parkir"></span>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold capitalize"
                                :class="detail.status === 'selesai' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (detail.status === 'parkir' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-rose-50 text-rose-700 border border-rose-200')"
                                x-text="detail.status">
                            </span>
                        </div>
                    </div>

                    <!-- Grid Detail Informasi -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] text-slate-400">Waktu Masuk</span>
                            <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" x-text="detail.waktu_masuk"></p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] text-slate-400">Waktu Keluar</span>
                            <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" x-text="detail.waktu_keluar || 'Sedang Parkir'"></p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] text-slate-400">Durasi Parkir</span>
                            <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" x-text="detail.durasi_format || '-'"></p>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50/60 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                            <span class="text-[11px] text-slate-400">Metode Bayar</span>
                            <p class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" x-text="detail.metode_bayar || '-'"></p>
                        </div>
                    </div>

                    <!-- Pembayaran & Biaya -->
                    <div class="p-3.5 rounded-xl bg-blue-50/60 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/60 space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-slate-600 dark:text-slate-400">Total Tarif</span>
                            <span class="font-bold text-slate-900 dark:text-white" x-text="detail.total_bayar_formatted"></span>
                        </div>
                        <div class="flex justify-between text-[11px]">
                            <span class="text-slate-500">Uang Diterima</span>
                            <span class="text-slate-700 dark:text-slate-300" x-text="detail.uang_diterima"></span>
                        </div>
                        <div class="flex justify-between text-[11px]">
                            <span class="text-slate-500">Kembalian</span>
                            <span class="text-slate-700 dark:text-slate-300" x-text="detail.kembalian"></span>
                        </div>
                    </div>

                    <!-- Petugas Pencatat -->
                    <div class="text-[11px] text-slate-400 text-right">
                        Petugas Jaga: <strong class="text-slate-700 dark:text-slate-300" x-text="detail.petugas"></strong>
                    </div>
                </div>
            </template>

            <!-- Modal Footer -->
            <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="modalOpen = false" 
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl text-xs transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function rekapTransaksiPage() {
    return {
        modalOpen: false,
        modalLoading: false,
        detail: {},

        async showDetail(id) {
            this.modalOpen = true;
            this.modalLoading = true;
            try {
                const res = await fetch('{{ url("/owner/transaksi") }}/' + id, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('Gagal mengambil data detail');
                this.detail = await res.json();
            } catch (err) {
                console.error(err);
                alert('Gagal memuat detail transaksi!');
                this.modalOpen = false;
            } finally {
                this.modalLoading = false;
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            }
        }
    };
}
</script>
@endpush
