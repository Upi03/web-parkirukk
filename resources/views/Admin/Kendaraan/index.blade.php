@extends('layouts.dashboard')

@section('title', 'Monitoring Kendaraan - SmarPARK')

@section('content')
<div class="flex flex-col gap-6" x-data="{
    showDetailModal: false,
    detailData: {
        id: '',
        plat_nomor: '',
        kode_tiket: '',
        jenis_kendaraan: '',
        area_nama: '',
        petugas_nama: '',
        waktu_masuk: '',
        waktu_keluar: '',
        durasi_menit: 0,
        total_bayar: 0,
        metode_bayar: '',
        uang_diterima: 0,
        kembalian: 0,
        status: ''
    },
    openDetail(item, estimasiBiaya, estimasiJam) {
        this.detailData = {
            id: item.id,
            plat_nomor: item.plat_nomor,
            kode_tiket: item.kode_tiket,
            jenis_kendaraan: item.jenis_kendaraan,
            area_nama: item.area_parkir ? item.area_parkir.nama_area : 'Belum Ditentukan',
            petugas_nama: item.petugas ? item.petugas.name : 'Sistem',
            waktu_masuk: item.waktu_masuk,
            waktu_keluar: item.waktu_keluar,
            durasi_menit: item.durasi_menit ?? (estimasiJam * 60),
            total_bayar: item.total_bayar ?? estimasiBiaya,
            metode_bayar: item.metode_bayar ?? '-',
            uang_diterima: item.uang_diterima ?? 0,
            kembalian: item.kembalian ?? 0,
            status: item.status
        };
        this.showDetailModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Kendaraan</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Pantau seluruh kendaraan yang sedang terparkir atau riwayat transaksi keluar yang dicatat oleh petugas.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs font-semibold">
                <i data-lucide="info" class="w-4 h-4"></i> Input kendaraan masuk/keluar dilakukan oleh Petugas
            </span>
        </div>
    </div>

    <!-- 3 Kartu Ringkasan Monitoring -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Transaksi</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">{{ number_format($totalKendaraan, 0, ',', '.') }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Seluruh riwayat tercatat</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                <i data-lucide="car" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Sedang Parkir</p>
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ number_format($totalParkir, 0, ',', '.') }}</p>
                <p class="text-[11px] text-amber-600/80 dark:text-amber-400/80 mt-0.5">Kendaraan di dalam area</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <div>
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Selesai Hari Ini</p>
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($totalSelesaiHariIni, 0, ',', '.') }}</p>
                <p class="text-[11px] text-emerald-600/80 dark:text-emerald-400/80 mt-0.5">Kendaraan keluar hari ini</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <x-card>
        <!-- Toolbar & Filter -->
        <form method="GET" action="{{ route('admin.kendaraan.index') }}" class="flex flex-col gap-4 mb-6">
            <div class="flex flex-col md:flex-row gap-3">
                <div class="relative flex-1">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Plat Nomor atau Kode Tiket..." 
                        class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="">Semua Status</option>
                        <option value="parkir" {{ request('status') === 'parkir' ? 'selected' : '' }}>Sedang Parkir</option>
                        <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Sudah Keluar</option>
                    </select>

                    <select name="jenis" onchange="this.form.submit()" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="">Semua Jenis</option>
                        @foreach($tarifs as $t)
                            <option value="{{ $t->jenis_kendaraan }}" {{ request('jenis') === $t->jenis_kendaraan ? 'selected' : '' }}>{{ $t->jenis_kendaraan }}</option>
                        @endforeach
                    </select>

                    <select name="area_id" onchange="this.form.submit()" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="">Semua Area</option>
                        @foreach($areas as $a)
                            <option value="{{ $a->id }}" {{ request('area_id') == $a->id ? 'selected' : '' }}>{{ $a->nama_area }}</option>
                        @endforeach
                    </select>

                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" onchange="this.form.submit()" 
                        class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:text-white">

                    @if(request('search') || request('status') || request('jenis') || request('area_id') || request('tanggal'))
                        <a href="{{ route('admin.kendaraan.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 rounded-lg text-sm font-medium transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead class="[&_tr]:border-b border-slate-200 dark:border-slate-800">
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20 transition-colors">
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Kendaraan</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Area Parkir</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Petugas</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Waktu Masuk</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Waktu Keluar</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Biaya</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Status</th>
                        <th class="h-10 px-4 text-right font-medium text-slate-500 dark:text-slate-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    @forelse($kendaraans as $item)
                    @php
                        $calc = $item->hitungBiaya();
                        $estimasiBiaya = $calc['total_bayar'];
                        $estimasiJam = $calc['durasi_jam'];
                    @endphp
                    <tr class="border-b border-slate-200 dark:border-slate-800 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="p-4 align-middle">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $item->plat_nomor }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $item->kode_tiket }} &bull; {{ $item->jenis_kendaraan }}</div>
                        </td>
                        <td class="p-4 align-middle">
                            @if($item->areaParkir)
                                <span class="px-2 py-0.5 text-xs rounded-md bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 font-medium">
                                    {{ $item->areaParkir->nama_area }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="p-4 align-middle text-xs text-slate-600 dark:text-slate-300">
                            {{ optional($item->petugas)->name ?? 'Sistem' }}
                        </td>
                        <td class="p-4 align-middle text-slate-600 dark:text-slate-300 text-xs">
                            <div>{{ $item->waktu_masuk->translatedFormat('d M Y, H:i') }}</div>
                            <div class="text-slate-400">{{ $item->waktu_masuk->diffForHumans() }}</div>
                        </td>
                        <td class="p-4 align-middle text-slate-600 dark:text-slate-300 text-xs">
                            @if($item->waktu_keluar)
                                <div>{{ $item->waktu_keluar->translatedFormat('d M Y, H:i') }}</div>
                                <div class="text-slate-400">Durasi: {{ round($item->durasi_menit / 60, 1) }} Jam</div>
                            @else
                                <span class="text-amber-600 dark:text-amber-400 font-medium flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Sedang parkir
                                </span>
                            @endif
                        </td>
                        <td class="p-4 align-middle font-medium text-slate-900 dark:text-white">
                            @if($item->status === 'selesai')
                                <span>Rp {{ number_format($item->total_bayar, 0, ',', '.') }}</span>
                                <div class="text-[10px] text-slate-400 capitalize">{{ $item->metode_bayar ?? 'Tunai' }}</div>
                            @else
                                <span class="text-slate-500 text-xs italic">Est: Rp {{ number_format($estimasiBiaya, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="p-4 align-middle">
                            @if($item->status === 'parkir')
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                    Parkir
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    Selesai
                                </span>
                            @endif
                        </td>
                        <td class="p-4 align-middle text-right">
                            <div class="flex items-center justify-end">
                                <!-- Tombol Lihat Detail -->
                                <button type="button" @click="openDetail({{ json_encode($item) }}, {{ $estimasiBiaya }}, {{ $estimasiJam }})" 
                                    class="p-2 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors" title="Lihat Detail Transaksi">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-500">
                            <i data-lucide="car" class="w-8 h-8 mx-auto mb-3 opacity-20"></i>
                            Tidak ada data transaksi kendaraan yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $kendaraans->links() }}
        </div>
    </x-card>

    <!-- Modal Detail Kendaraan / Transaksi (Eye Icon) -->
    <div x-show="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.outside="showDetailModal = false" class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden border border-slate-200 dark:border-slate-700">
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50 dark:bg-slate-800/80">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Detail Transaksi Parkir</h3>
                </div>
                <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4">
                <!-- Highlight Header: Plat & Status -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-100 dark:border-slate-600 flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Plat Nomor</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white tracking-wide" x-text="detailData.plat_nomor"></div>
                        <div class="text-xs font-mono text-slate-500 dark:text-slate-300 mt-0.5" x-text="'Kode Tiket: ' + detailData.kode_tiket"></div>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full text-xs font-bold capitalize"
                            :class="detailData.status === 'parkir' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'"
                            x-text="detailData.status === 'parkir' ? 'Sedang Parkir' : 'Selesai'">
                        </span>
                        <div class="text-xs text-slate-400 mt-1" x-text="detailData.jenis_kendaraan"></div>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                        <div class="text-xs text-slate-400 font-medium">Area Parkir</div>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" x-text="detailData.area_nama"></div>
                    </div>

                    <div class="p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                        <div class="text-xs text-slate-400 font-medium">Petugas Pencatat</div>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" x-text="detailData.petugas_nama"></div>
                    </div>

                    <div class="p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                        <div class="text-xs text-slate-400 font-medium">Waktu Masuk</div>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" x-text="detailData.waktu_masuk ? new Date(detailData.waktu_masuk).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '-'"></div>
                    </div>

                    <div class="p-3 rounded-lg border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800">
                        <div class="text-xs text-slate-400 font-medium">Waktu Keluar</div>
                        <div class="font-semibold text-slate-800 dark:text-slate-200 mt-0.5" x-text="detailData.waktu_keluar ? new Date(detailData.waktu_keluar).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : 'Masih parkir'"></div>
                    </div>
                </div>

                <!-- Billing Breakdown -->
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 space-y-2">
                    <div class="flex justify-between items-center text-xs text-slate-500">
                        <span>Durasi Parkir</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="Math.round(detailData.durasi_menit / 60 * 10) / 10 + ' Jam (' + detailData.durasi_menit + ' menit)'"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs text-slate-500">
                        <span>Metode Pembayaran</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="detailData.metode_bayar"></span>
                    </div>
                    <template x-if="detailData.status === 'selesai' && detailData.uang_diterima > 0">
                        <div class="flex justify-between items-center text-xs text-slate-500">
                            <span>Uang Diterima / Bayar</span>
                            <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="'Rp ' + Number(detailData.uang_diterima).toLocaleString('id-ID')"></span>
                        </div>
                    </template>
                    <template x-if="detailData.status === 'selesai' && detailData.kembalian > 0">
                        <div class="flex justify-between items-center text-xs text-slate-500">
                            <span>Kembalian</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="'Rp ' + Number(detailData.kembalian).toLocaleString('id-ID')"></span>
                        </div>
                    </template>
                    <div class="border-t border-slate-200 dark:border-slate-700 pt-2 flex justify-between items-center">
                        <span class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                            <span x-text="detailData.status === 'parkir' ? 'Estimasi Total Biaya' : 'Total Biaya Parkir'"></span>
                        </span>
                        <span class="text-lg font-extrabold text-blue-600 dark:text-blue-400" x-text="'Rp ' + Number(detailData.total_bayar).toLocaleString('id-ID')"></span>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3 bg-slate-50 dark:bg-slate-800 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                <button type="button" @click="showDetailModal = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 rounded-lg text-sm font-medium transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
