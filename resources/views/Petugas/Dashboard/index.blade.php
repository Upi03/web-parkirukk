@extends('layouts.dashboard')

@section('title', 'Dashboard Petugas - SmarPARK')

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header Content -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Dashboard</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Selamat bekerja, {{ auth()->user()->name }}! Berikut ringkasan hari ini ({{ $serverDate }}).</p>
        </div>
    </div>

    <!-- 4 Kartu Statistik -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card 
            title="Kendaraan Masuk" 
            :value="$stats['kendaraan_masuk']['value']" 
            icon="log-in" 
            :trend="$stats['kendaraan_masuk']['trend']" 
            :percentage="$stats['kendaraan_masuk']['percentage']" 
        />
        <x-stat-card 
            title="Sedang Parkir" 
            :value="$stats['sedang_parkir']['value']" 
            icon="car" 
            :trend="$stats['sedang_parkir']['trend']" 
            :percentage="$stats['sedang_parkir']['percentage']" 
        />
        <x-stat-card 
            title="Kendaraan Keluar" 
            :value="$stats['kendaraan_keluar']['value']" 
            icon="log-out" 
            :trend="$stats['kendaraan_keluar']['trend']" 
            :percentage="$stats['kendaraan_keluar']['percentage']" 
        />
        <x-stat-card 
            title="Pendapatan Hari Ini" 
            :value="'Rp ' . number_format($stats['pendapatan']['value'], 0, ',', '.')" 
            icon="banknote" 
            :trend="$stats['pendapatan']['trend']" 
            :percentage="$stats['pendapatan']['percentage']" 
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-4">
        <!-- Tabel Transaksi Terakhir -->
        <x-card class="lg:col-span-2">
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold leading-none tracking-tight">Transaksi Terakhir (Selesai)</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">5 riwayat pembayaran tiket terbaru hari ini.</p>
                    </div>
                    <a href="{{ route('petugas.transaksi.index', ['status' => 'selesai']) }}" class="text-sm text-blue-600 hover:underline font-medium flex items-center gap-1">
                        Lihat Semua <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </x-slot>
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b border-slate-200 dark:border-slate-800">
                        <tr class="border-b transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Kode Tiket</th>
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Plat</th>
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Total</th>
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Metode</th>
                            <th class="h-10 px-4 text-right align-middle font-medium text-slate-500 dark:text-slate-400">Waktu Keluar</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        @forelse($recentTransactions as $t)
                        <tr class="border-b border-slate-200 dark:border-slate-800 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="p-4 align-middle font-medium text-blue-600 dark:text-blue-400">{{ $t->kode_tiket }}</td>
                            <td class="p-4 align-middle font-bold">{{ $t->plat_nomor }}</td>
                            <td class="p-4 align-middle font-medium text-slate-900 dark:text-white">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</td>
                            <td class="p-4 align-middle">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ $t->metode_bayar ?? 'Tunai' }}
                                </span>
                            </td>
                            <td class="p-4 align-middle text-right text-slate-500">{{ $t->waktu_keluar ? $t->waktu_keluar->format('H:i') : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500">
                                <i data-lucide="receipt" class="w-8 h-8 mx-auto mb-3 opacity-20"></i>
                                Belum ada transaksi selesai.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card>
            <x-slot name="header">
                <h3 class="font-semibold leading-none tracking-tight">Ketersediaan Kapasitas</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Estimasi slot parkir saat ini dari database.</p>
            </x-slot>
            <div class="flex flex-col gap-4 mt-2">
                @forelse($slotAvailability as $item)
                @php
                    $isMotor = strtolower($item['jenis']) === 'motor';
                    $bgCard = $isMotor ? 'bg-cyan-50 dark:bg-cyan-900/20 border-cyan-100 dark:border-cyan-800' : 'bg-blue-50 dark:bg-blue-900/20 border-blue-100 dark:border-blue-800';
                    $textColor = $isMotor ? 'text-cyan-900 dark:text-cyan-100' : 'text-blue-900 dark:text-blue-100';
                    $badgeColor = $isMotor ? 'text-cyan-700 dark:text-cyan-300' : 'text-blue-700 dark:text-blue-300';
                    $barBg = $isMotor ? 'bg-cyan-200 dark:bg-cyan-950' : 'bg-blue-200 dark:bg-blue-950';
                    $barColor = $isMotor ? 'bg-cyan-500' : 'bg-blue-600';
                    $icon = $isMotor ? 'bike' : (str_contains(strtolower($item['jenis']), 'truk') ? 'truck' : 'car');
                @endphp
                <div class="p-4 rounded-lg {{ $bgCard }} border">
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center gap-2 font-semibold {{ $textColor }}">
                            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i> {{ $item['jenis'] }}
                        </div>
                        <span class="text-sm font-medium {{ $badgeColor }}">{{ $item['kosong'] }} Kosong</span>
                    </div>
                    <div class="w-full {{ $barBg }} rounded-full h-2 overflow-hidden">
                        <div class="{{ $barColor }} h-2 rounded-full transition-all duration-300" style="width: {{ $item['persentase'] }}%"></div>
                    </div>
                    <div class="flex justify-between items-center text-xs text-slate-500 mt-2">
                        <span>Kapasitas: {{ $item['kapasitas'] }}</span>
                        <span class="font-medium">{{ $item['terisi'] }} Terisi ({{ $item['persentase'] }}%)</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-6 text-sm text-slate-400">
                    Belum ada data tarif / kapasitas area.
                </div>
                @endforelse

                @if(count($areaSummaries) > 0)
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Status Per Area</p>
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        @foreach($areaSummaries as $ar)
                        <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 dark:bg-slate-800/60 text-xs">
                            <div>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $ar['nama'] }}</span>
                                <span class="text-slate-400 block text-[11px]">{{ $ar['terisi'] }}/{{ $ar['kapasitas'] }} terisi</span>
                            </div>
                            <span class="px-2 py-0.5 rounded font-semibold text-[10px] {{ $ar['tersedia'] <= 0 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' }}">
                                {{ $ar['tersedia'] <= 0 ? 'Penuh' : $ar['tersedia'] . ' Sisa' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </x-card>
    </div>
</div>
@endsection
