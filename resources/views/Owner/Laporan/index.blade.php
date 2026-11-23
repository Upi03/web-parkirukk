@extends('layouts.dashboard')

@section('title', 'Laporan Finansial & Operasional - Owner')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons (Hidden during Print) -->
    <div class="print:hidden flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-xs">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-xl">
                <i data-lucide="file-bar-chart" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Laporan Eksekutif Parkir</h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Periode: <span class="font-bold text-slate-800 dark:text-slate-200">{{ $startDate->translatedFormat('F Y') }}</span>
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Filter Bulan & Tahun Form -->
            <form method="GET" action="{{ route('owner.laporan.index') }}" class="flex items-center gap-2 bg-slate-50 dark:bg-slate-800 p-1 rounded-xl border border-slate-200 dark:border-slate-700 text-xs">
                <select name="month" class="bg-transparent border-0 text-slate-700 dark:text-slate-200 font-medium focus:ring-0 text-xs px-2 py-1">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>

                <select name="year" class="bg-transparent border-0 text-slate-700 dark:text-slate-200 font-medium focus:ring-0 text-xs px-2 py-1">
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" class="px-3 py-1 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Lihat
                </button>
            </form>

            <!-- Export CSV -->
            <a href="{{ route('owner.laporan.export', ['month' => $month, 'year' => $year]) }}" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Export CSV</span>
            </a>

            <!-- Tombol Cetak / PDF Print View -->
            <button type="button" onclick="window.print()" 
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-xs shadow-blue-500/20 transition-colors">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- KOP RESMI LAPORAN (Khusus Ditampilkan saat Cetak) -->
    <div class="hidden print:block text-center border-b-2 border-slate-900 pb-4 mb-6">
        <h2 class="text-2xl font-black uppercase tracking-wider text-slate-900">SmarPARK MANAGEMENT SYSTEM</h2>
        <p class="text-xs text-slate-600">Laporan Eksekutif Rekapitulasi Finansial & Operasional Parkir Kendaraan</p>
        <p class="text-xs font-semibold text-slate-800 mt-1">Periode: {{ $startDate->translatedFormat('F Y') }}</p>
    </div>

    <!-- 4 RINGKASAN EKSEKUTIF UTAMA -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Omset Bersih</span>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                Rp {{ number_format($totalPendapatan, 0, ',', '.') }}
            </h3>
            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                <span>Transaksi selesai terverifikasi</span>
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Kendaraan Selesai</span>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                {{ number_format($totalSelesai, 0, ',', '.') }} <span class="text-sm font-semibold text-slate-500">Unit</span>
            </h3>
            <p class="text-[11px] text-slate-400 mt-1">
                Dari total {{ number_format($totalTransaksi, 0, ',', '.') }} tiket diterbitkan
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Rata-rata Pendapatan/Unit</span>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                Rp {{ number_format($avgPerTransaksi, 0, ',', '.') }}
            </h3>
            <p class="text-[11px] text-slate-400 mt-1">Estimasi nilai rata-rata tiket</p>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Rata-rata Durasi</span>
            <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                @php
                    $avgH = floor($avgDurasi / 60);
                    $avgM = round($avgDurasi % 60);
                @endphp
                {{ $avgH > 0 ? "{$avgH} jam " : '' }}{{ $avgM }} mnt
            </h3>
            <p class="text-[11px] text-slate-400 mt-1">Per kendaraan selesai</p>
        </div>
    </div>

    <!-- SEKSI 1: KONTRIBUSI JENIS KENDARAAN -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 pb-4 border-b border-slate-100 dark:border-slate-800">
            <i data-lucide="pie-chart" class="w-4 h-4 text-blue-600"></i>
            Rincian Berdasarkan Jenis Kendaraan
        </h2>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500">
                    <tr>
                        <th class="px-4 py-3 rounded-l-xl">Kategori Kendaraan</th>
                        <th class="px-4 py-3 text-center">Jumlah Unit</th>
                        <th class="px-4 py-3">Total Pendapatan (Rp)</th>
                        <th class="px-4 py-3">Kontribusi Terhadap Omset</th>
                        <th class="px-4 py-3 rounded-r-xl text-center">Persentase</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($kendaraanReport as $k)
                    <tr>
                        <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $k->jenis_kendaraan === 'Mobil' ? 'bg-blue-600' : ($k->jenis_kendaraan === 'Motor' ? 'bg-cyan-500' : 'bg-amber-500') }}"></span>
                            {{ $k->jenis_kendaraan }}
                        </td>
                        <td class="px-4 py-3.5 text-center font-semibold text-slate-800 dark:text-slate-200">
                            {{ number_format($k->total_unit, 0, ',', '.') }} unit
                        </td>
                        <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white">
                            Rp {{ number_format($k->total_omset, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3.5">
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full {{ $k->jenis_kendaraan === 'Mobil' ? 'bg-blue-600' : ($k->jenis_kendaraan === 'Motor' ? 'bg-cyan-500' : 'bg-amber-500') }}"
                                    style="width: {{ $k->persentase }}%"></div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center font-bold text-slate-900 dark:text-white">
                            {{ $k->persentase }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-400">Tidak ada data transaksi di periode ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SEKSI 2: AREA PARKIR & KINERJA PETUGAS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Per Area Parkir -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 pb-4 border-b border-slate-100 dark:border-slate-800">
                <i data-lucide="map-pin" class="w-4 h-4 text-emerald-600"></i>
                Pemanfaatan Area Parkir
            </h2>
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500">
                        <tr>
                            <th class="px-3 py-2.5">Area</th>
                            <th class="px-3 py-2.5 text-center">Unit Selesai</th>
                            <th class="px-3 py-2.5 text-right">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($areaReport as $a)
                        <tr>
                            <td class="px-3 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $a->areaParkir ? $a->areaParkir->nama_area : 'Tanpa Area' }}
                            </td>
                            <td class="px-3 py-3 text-center text-slate-600 dark:text-slate-400">
                                {{ number_format($a->total_unit, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right font-bold text-slate-900 dark:text-white">
                                Rp {{ number_format($a->total_omset, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-center text-slate-400">Belum ada data area</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rekap Penerimaan Petugas -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
            <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 pb-4 border-b border-slate-100 dark:border-slate-800">
                <i data-lucide="users" class="w-4 h-4 text-indigo-600"></i>
                Rekap Penerimaan Petugas Jaga
            </h2>
            <div class="overflow-x-auto mt-4">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500">
                        <tr>
                            <th class="px-3 py-2.5">Nama Petugas</th>
                            <th class="px-2 py-2.5 text-center">Transaksi</th>
                            <th class="px-3 py-2.5 text-right">Uang Tunai</th>
                            <th class="px-3 py-2.5 text-right">Non-Tunai</th>
                            <th class="px-3 py-2.5 text-right">Total Setor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($petugasReport as $p)
                        <tr>
                            <td class="px-3 py-3 font-semibold text-slate-800 dark:text-slate-200">
                                {{ $p->petugas ? $p->petugas->name : 'Sistem' }}
                            </td>
                            <td class="px-2 py-3 text-center text-slate-600 dark:text-slate-400">
                                {{ $p->total_transaksi }}
                            </td>
                            <td class="px-3 py-3 text-right text-slate-600 dark:text-slate-300">
                                Rp {{ number_format($p->total_tunai, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right text-cyan-600 dark:text-cyan-400">
                                Rp {{ number_format($p->total_nontunai, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right font-bold text-slate-900 dark:text-white">
                                Rp {{ number_format($p->total_setoran, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-3 py-4 text-center text-slate-400">Belum ada data petugas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- SEKSI 3: RINCIAN HARIAN (KALENDER TRANSAKSI) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-6 shadow-xs">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-cyan-600"></i>
                    Rincian Pendapatan Harian
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Daftar rekapitulasi penerimaan kas dan digital per hari</p>
            </div>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-800/60 uppercase font-semibold text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Hari</th>
                        <th class="px-4 py-3 text-center">Kendaraan Masuk</th>
                        <th class="px-4 py-3 text-center">Kendaraan Keluar</th>
                        <th class="px-4 py-3 text-right">Penerimaan Tunai</th>
                        <th class="px-4 py-3 text-right">Penerimaan QRIS</th>
                        <th class="px-4 py-3 text-right">Total Omset Harian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($dailyReport as $day)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                        <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">
                            {{ $day['tanggal'] }}
                        </td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ $day['hari'] }}
                        </td>
                        <td class="px-4 py-3 text-center text-slate-700 dark:text-slate-300">
                            {{ $day['masuk'] }} unit
                        </td>
                        <td class="px-4 py-3 text-center text-slate-700 dark:text-slate-300">
                            {{ $day['keluar'] }} unit
                        </td>
                        <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-400">
                            Rp {{ number_format($day['tunai'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-cyan-600 dark:text-cyan-400 font-medium">
                            Rp {{ number_format($day['qris'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">
                            Rp {{ number_format($day['omset'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-400">Tidak ada riwayat harian</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TANDA TANGAN (Khusus Print) -->
    <div class="hidden print:grid grid-cols-2 pt-12 text-center text-xs">
        <div>
            <p>Mengetahui,</p>
            <p class="font-bold text-slate-900 mt-16">{{ auth()->user()->name ?? 'Owner SmarPARK' }}</p>
            <p class="text-[11px] text-slate-500">Owner & Pemilik Sistem</p>
        </div>
        <div>
            <p>Dicetak pada: {{ now()->translatedFormat('d F Y') }}</p>
            <p class="font-bold text-slate-900 mt-16">( .................................................. )</p>
            <p class="text-[11px] text-slate-500">Supervisor Operasional</p>
        </div>
    </div>

</div>
@endsection
