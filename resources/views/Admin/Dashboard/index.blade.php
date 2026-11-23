@extends('layouts.dashboard')

@section('title', 'Dashboard Admin - SmarPARK')

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header Content -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Dashboard</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Halo, {{ auth()->user()->name }} 👋! Berikut ringkasan sistem hari ini.</p>
        </div>
    </div>

    <!-- 4 Kartu Statistik -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card 
            title="Total Slot Parkir" 
            :value="$stats['total_slot']['value']" 
            icon="car" 
            :trend="$stats['total_slot']['trend']" 
            :percentage="$stats['total_slot']['info']" 
        />
        <x-stat-card 
            title="Kendaraan Masuk Hari Ini" 
            :value="$stats['kendaraan_hari_ini']['value']" 
            icon="log-in" 
            :trend="$stats['kendaraan_hari_ini']['trend']" 
            :percentage="$stats['kendaraan_hari_ini']['info']" 
        />
        <x-stat-card 
            title="Pendapatan Hari Ini" 
            :value="'Rp ' . number_format($stats['pendapatan_hari_ini']['value'], 0, ',', '.')" 
            icon="banknote" 
            :trend="$stats['pendapatan_hari_ini']['trend']" 
            :percentage="$stats['pendapatan_hari_ini']['info']" 
        />
        <x-stat-card 
            title="Total Petugas Aktif" 
            :value="$stats['total_petugas']['value']" 
            icon="users" 
            :trend="$stats['total_petugas']['trend']" 
            :percentage="$stats['total_petugas']['info']" 
        />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
        <!-- Grafik Bar -->
        <x-card>
            <x-slot name="header">
                <h3 class="font-semibold leading-none tracking-tight">Kendaraan Masuk per Jam</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Total kendaraan yang masuk hari ini (06:00 - 22:00).</p>
            </x-slot>
            <div class="relative w-full overflow-hidden mt-4">
                <canvas id="barChart" height="250"></canvas>
            </div>
        </x-card>

        <!-- Grafik Garis -->
        <x-card>
            <x-slot name="header">
                <h3 class="font-semibold leading-none tracking-tight">Pendapatan 7 Hari Terakhir</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tren pendapatan transaksi selesai (Rp).</p>
            </x-slot>
            <div class="relative w-full overflow-hidden mt-4">
                <canvas id="lineChart" height="250"></canvas>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-4">
        <!-- Tabel Aktivitas Terbaru -->
        <x-card class="lg:col-span-2">
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold leading-none tracking-tight">Aktivitas Terbaru</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Riwayat aksi yang tercatat di sistem.</p>
                    </div>
                    <a href="{{ route('admin.log.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 hover:underline">Lihat Semua</a>
                </div>
            </x-slot>
            <div class="relative w-full overflow-auto mt-4">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b border-slate-200 dark:border-slate-800">
                        <tr class="border-b bg-slate-50/50 dark:bg-slate-800/20 transition-colors">
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Waktu</th>
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">User / Petugas</th>
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Aksi</th>
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        @forelse($recentActivities as $activity)
                        <tr class="border-b border-slate-200 dark:border-slate-800 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="p-4 align-middle text-slate-500 whitespace-nowrap">{{ $activity->created_at->diffForHumans() }}</td>
                            <td class="p-4 align-middle font-medium whitespace-nowrap">{{ optional($activity->user)->name ?? 'Sistem' }}</td>
                            <td class="p-4 align-middle whitespace-nowrap">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $activity->action_badge_class }}">
                                    {{ $activity->action }}
                                </span>
                            </td>
                            <td class="p-4 align-middle text-slate-600 dark:text-slate-300">{{ $activity->description }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-500">
                                <i data-lucide="history" class="w-8 h-8 mx-auto mb-3 opacity-20"></i>
                                Belum ada aktivitas yang tercatat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Ringkasan Area Parkir -->
        <x-card>
            <x-slot name="header">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold leading-none tracking-tight">Status Area Parkir</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kapasitas di setiap area saat ini.</p>
                    </div>
                    <a href="{{ route('admin.area.index') }}" class="text-xs text-blue-600 hover:underline">Kelola</a>
                </div>
            </x-slot>
            <div class="flex flex-col gap-3 mt-4">
                @forelse($areaSummary as $area)
                <div class="p-4 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $area['nama'] }}</span>
                        @if($area['status'] === 'Penuh')
                            <span class="px-2 py-0.5 text-[10px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 rounded-full">Penuh</span>
                        @else
                            <span class="px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 rounded-full">Tersedia</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 dark:text-slate-400 mb-2">Terisi {{ $area['terisi'] }} dari {{ $area['kapasitas'] }} Slot ({{ $area['persentase'] }}%)</div>
                    <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-500 {{ $area['persentase'] >= 100 ? 'bg-red-500' : ($area['persentase'] >= 80 ? 'bg-amber-500' : 'bg-blue-600') }}" 
                            style="width: {{ min(100, $area['persentase']) }}%"></div>
                    </div>
                </div>
                @empty
                <div class="p-6 text-center text-sm text-slate-500">
                    <i data-lucide="map-pin" class="w-8 h-8 mx-auto mb-2 opacity-20"></i>
                    Belum ada area parkir yang ditambahkan.
                </div>
                @endforelse
            </div>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data for Bar Chart (Kendaraan Masuk)
        const ctxBar = document.getElementById('barChart');
        if (ctxBar) {
            new Chart(ctxBar.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartMasukPerJam['labels']) !!},
                    datasets: [{
                        label: 'Jumlah Kendaraan',
                        data: {!! json_encode($chartMasukPerJam['data']) !!},
                        backgroundColor: '#2563EB',
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            ticks: { precision: 0 },
                            grid: { borderDash: [2, 4], color: '#e2e8f0' } 
                        },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // Data for Line Chart (Pendapatan 7 Hari Terakhir)
        const ctxLine = document.getElementById('lineChart');
        if (ctxLine) {
            new Chart(ctxLine.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! json_encode($chartPendapatan['labels']) !!},
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: {!! json_encode($chartPendapatan['data']) !!},
                        borderColor: '#06B6D4',
                        backgroundColor: 'rgba(6, 182, 212, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { borderDash: [2, 4], color: '#e2e8f0' },
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                                }
                            }
                        },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Pendapatan: Rp ' + Number(context.raw).toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
