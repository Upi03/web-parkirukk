@extends('layouts.dashboard')

@section('title', ($type === 'masuk' ? 'Cetak Struk Masuk' : 'Cetak Struk Keluar') . ' - SmarPARK')

@section('content')
<div class="flex flex-col gap-6">
    <!-- Header Content -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                {{ $type === 'masuk' ? 'Cetak Struk Masuk' : 'Cetak Struk Keluar' }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ $type === 'masuk' 
                    ? 'Daftar karcis / struk tiket kendaraan masuk yang dapat dicetak ulang.' 
                    : 'Daftar struk bukti pembayaran kendaraan keluar yang dapat dicetak ulang.' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('petugas.transaksi.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Ke Transaksi Parkir
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
        <i data-lucide="check-circle" class="w-4 h-4"></i>
        {{ session('success') }}
    </div>
    @endif

    <!-- Tab Pilihan: Cetak Struk Masuk & Cetak Struk Keluar -->
    <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800">
        <a href="{{ route('petugas.struk.masuk') }}"
           class="pb-3 px-2 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors {{ $type === 'masuk' ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <i data-lucide="ticket" class="w-4 h-4 {{ $type === 'masuk' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}"></i>
            <span>Cetak Struk Masuk</span>
            <span class="ml-1 rounded-full px-2 py-0.5 text-xs {{ $type === 'masuk' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                {{ $totalMasuk }}
            </span>
        </a>
        <a href="{{ route('petugas.struk.keluar') }}"
           class="pb-3 px-2 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors {{ $type === 'keluar' ? 'border-blue-600 text-blue-600 dark:text-blue-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
            <i data-lucide="receipt" class="w-4 h-4 {{ $type === 'keluar' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400' }}"></i>
            <span>Cetak Struk Keluar</span>
            <span class="ml-1 rounded-full px-2 py-0.5 text-xs {{ $type === 'keluar' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                {{ $totalKeluar }}
            </span>
        </a>
    </div>

    <x-card>
        <x-slot name="header">
            <form action="{{ $type === 'masuk' ? route('petugas.struk.masuk') : route('petugas.struk.keluar') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full">
                <input type="hidden" name="type" value="{{ $type }}">
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-500"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari plat atau kode tiket..." class="flex h-9 w-full rounded-md border border-slate-200 dark:border-slate-800 bg-transparent px-3 py-1 pl-9 text-xs shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-blue-500 dark:text-white uppercase">
                </div>

                @if($type === 'masuk')
                <div>
                    <select name="status" onchange="this.form.submit()" class="flex h-9 w-full rounded-md border border-slate-200 dark:border-slate-800 bg-transparent px-3 py-1 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-blue-500 dark:text-white">
                        <option value="">Semua Status Kendaraan</option>
                        <option value="parkir" {{ request('status') === 'parkir' ? 'selected' : '' }}>Sedang Parkir ({{ $totalSedangParkir }})</option>
                        <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Sudah Selesai ({{ $totalKeluar }})</option>
                    </select>
                </div>
                @endif

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" onchange="this.form.submit()" class="flex h-9 rounded-md border border-slate-200 dark:border-slate-800 bg-transparent px-3 py-1 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-blue-500 dark:text-white">
                    <button type="submit" class="inline-flex items-center justify-center rounded-md border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-3 py-1.5 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        Cari
                    </button>
                    @if(request()->hasAny(['search', 'tanggal', 'status']))
                        <a href="{{ $type === 'masuk' ? route('petugas.struk.masuk') : route('petugas.struk.keluar') }}" class="text-xs text-red-500 hover:underline shrink-0">Reset</a>
                    @endif
                </div>
            </form>
        </x-slot>
        
        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead class="[&_tr]:border-b border-slate-200 dark:border-slate-800">
                    <tr class="border-b transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                        <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Kode & Plat</th>
                        <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Jenis & Area</th>
                        @if($type === 'masuk')
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Waktu Masuk</th>
                            <th class="h-10 px-4 text-center align-middle font-medium text-slate-500 dark:text-slate-400">Status</th>
                        @else
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Waktu Keluar & Durasi</th>
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Total Pembayaran</th>
                        @endif
                        <th class="h-10 px-4 text-right align-middle font-medium text-slate-500 dark:text-slate-400">Aksi Cetak</th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    @forelse($transaksis as $t)
                    <tr class="border-b border-slate-200 dark:border-slate-800 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="p-4 align-middle">
                            <span class="font-bold text-slate-900 dark:text-white block">{{ $t->plat_nomor }}</span>
                            <span class="text-xs text-blue-600 dark:text-blue-400 font-mono mt-0.5 block">{{ $t->kode_tiket }}</span>
                        </td>
                        <td class="p-4 align-middle text-xs">
                            <span class="font-semibold text-slate-800 dark:text-slate-200 block">{{ $t->jenis_kendaraan }}</span>
                            <span class="text-slate-500 mt-0.5 flex items-center gap-1">
                                <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                {{ $t->areaParkir->nama_area ?? 'Area Umum' }}
                            </span>
                        </td>

                        @if($type === 'masuk')
                            <td class="p-4 align-middle text-xs text-slate-600 dark:text-slate-300">
                                {{ $t->waktu_masuk ? $t->waktu_masuk->format('d M Y, H:i') : '-' }}
                            </td>
                            <td class="p-4 align-middle text-center">
                                @if($t->status === 'parkir')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                        Sedang Parkir
                                    </span>
                                @elseif($t->status === 'selesai')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                        Batal
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 align-middle text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('petugas.struk.show', ['transaksi' => $t->id, 'type' => 'masuk']) }}" target="_blank"
                                       class="inline-flex items-center justify-center rounded-md text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white h-8 px-3 gap-1.5 shadow-sm transition-colors">
                                        <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
                                        Cetak Struk Masuk
                                    </a>
                                    @if($t->status === 'selesai')
                                        <a href="{{ route('petugas.struk.show', ['transaksi' => $t->id, 'type' => 'keluar']) }}" target="_blank"
                                           class="inline-flex items-center justify-center rounded-md text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 h-8 px-2.5 transition-colors gap-1"
                                           title="Cetak Struk Keluar">
                                            <i data-lucide="printer" class="w-3.5 h-3.5 text-emerald-500"></i>
                                            <span class="hidden sm:inline">Struk Keluar</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        @else
                            <td class="p-4 align-middle text-xs">
                                <span class="font-medium text-slate-800 dark:text-slate-200 block">
                                    {{ $t->waktu_keluar ? $t->waktu_keluar->format('d M Y, H:i') : '-' }}
                                </span>
                                @php
                                    $durasiMenit = $t->durasi_menit ?? 0;
                                    $durasiTeks = floor($durasiMenit / 60) . ' Jam ' . ($durasiMenit % 60) . ' Menit';
                                @endphp
                                <span class="text-slate-500 block text-[11px] mt-0.5">Durasi: {{ $durasiTeks }}</span>
                            </td>
                            <td class="p-4 align-middle font-medium text-slate-900 dark:text-white">
                                Rp {{ number_format($t->total_bayar, 0, ',', '.') }}
                                <span class="block text-xs font-normal text-slate-500 mt-0.5">{{ $t->metode_bayar ?? 'Tunai' }}</span>
                            </td>
                            <td class="p-4 align-middle text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('petugas.struk.show', ['transaksi' => $t->id, 'type' => 'keluar']) }}" target="_blank"
                                       class="inline-flex items-center justify-center rounded-md text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white h-8 px-3 gap-1.5 shadow-sm transition-colors">
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        Cetak Struk Keluar
                                    </a>
                                    <a href="{{ route('petugas.struk.show', ['transaksi' => $t->id, 'type' => 'masuk']) }}" target="_blank"
                                       class="inline-flex items-center justify-center rounded-md text-xs font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 h-8 px-2.5 transition-colors gap-1"
                                       title="Cetak Ulang Struk Masuk">
                                        <i data-lucide="ticket" class="w-3.5 h-3.5 text-blue-500"></i>
                                        <span class="hidden sm:inline">Struk Masuk</span>
                                    </a>
                                </div>
                            </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">
                            <i data-lucide="{{ $type === 'masuk' ? 'ticket' : 'receipt' }}" class="w-8 h-8 mx-auto mb-3 opacity-20"></i>
                            {{ $type === 'masuk' ? 'Belum ada data karcis / struk masuk.' : 'Belum ada transaksi pembayaran keluar selesai.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transaksis->hasPages())
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800">
            {{ $transaksis->links() }}
        </div>
        @endif
    </x-card>
</div>
@endsection
