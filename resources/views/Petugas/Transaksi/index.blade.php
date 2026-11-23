@extends('layouts.dashboard')

@section('title', 'Transaksi Parkir - SmarPARK')

@section('content')
    <div class="flex flex-col gap-6" x-data="{ 
        checkoutData: null,
        uangDiterima: '',
        metodeBayar: 'Tunai',
        searchQuery: '',
        searchLoading: false,
        searchError: '',
        cancelData: null,
        alasanBatal: '',

        get kembalian() {
            if (!this.checkoutData || this.metodeBayar !== 'Tunai' || !this.uangDiterima) return 0;
            let diff = parseFloat(this.uangDiterima) - this.checkoutData.total_bayar;
            return diff > 0 ? diff : 0;
        },

        get isBayarValid() {
            if (!this.checkoutData) return false;
            if (this.metodeBayar === 'Tunai') {
                return this.uangDiterima && parseFloat(this.uangDiterima) >= this.checkoutData.total_bayar;
            }
            return true;
        },

        openCheckout(tx) {
            this.checkoutData = tx;
            this.uangDiterima = '';
            this.metodeBayar = 'Tunai';
            this.searchError = '';
            $dispatch('open-modal', 'modal-keluar');
        },

        openCancel(tx) {
            this.cancelData = tx;
            this.alasanBatal = '';
            $dispatch('open-modal', 'modal-batal');
        },

        async searchVehicle() {
            if (!this.searchQuery || this.searchQuery.trim().length < 2) {
                this.searchError = 'Ketik minimal 2 karakter kode tiket atau plat nomor.';
                return;
            }
            this.searchLoading = true;
            this.searchError = '';
            try {
                const res = await fetch('{{ route('petugas.transaksi.cek-parkir') }}?q=' + encodeURIComponent(this.searchQuery.trim()));
                const json = await res.json();
                if (json.success && json.data) {
                    this.checkoutData = json.data;
                    this.uangDiterima = '';
                    this.metodeBayar = 'Tunai';
                } else {
                    this.searchError = json.message || 'Kendaraan tidak ditemukan.';
                }
            } catch (err) {
                this.searchError = 'Terjadi kesalahan saat mencari kendaraan.';
            } finally {
                this.searchLoading = false;
            }
        }
    }">
        <!-- Header Content -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Transaksi Parkir</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Kelola kendaraan masuk, proses checkout pembayaran keluar, dan audit tiket parkir.</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Tombol Kendaraan Keluar (Cari Cepat) -->
                <button type="button" @click="checkoutData = null; searchQuery = ''; searchError = ''; $dispatch('open-modal', 'modal-keluar')"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors gap-2">
                    <i data-lucide="log-out" class="w-4 h-4 text-amber-500"></i>
                    Kendaraan Keluar
                </button>

                <!-- Tombol Kendaraan Masuk -->
                <button type="button" @click="$dispatch('open-modal', 'modal-masuk')"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors gap-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Kendaraan Masuk
                </button>
            </div>
        </div>

        <!-- Banner Sukses Masuk dengan Tombol Cetak Tiket / Struk Masuk -->
        @if(session('ticket_masuk_id'))
            <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-900 dark:text-blue-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0">
                        <i data-lucide="ticket" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Struk Masuk Siap Dicetak!</p>
                        <p class="text-xs text-blue-700 dark:text-blue-300">Cetak karcis / struk masuk untuk diberikan kepada pengemudi kendaraan.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('petugas.struk.show', ['transaksi' => session('ticket_masuk_id'), 'type' => 'masuk']) }}" target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i> Cetak Struk Masuk
                    </a>
                </div>
            </div>
        @endif

        <!-- Card Filter & Tabel -->
        <x-card class="overflow-visible">
            <x-slot name="header">
                <form action="{{ route('petugas.transaksi.index') }}" method="GET"
                    class="flex flex-col gap-3 w-full">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <!-- Tab Status Cepat -->
                        <div class="flex items-center gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-lg text-xs font-medium">
                            <a href="{{ route('petugas.transaksi.index') }}"
                                class="px-3 py-1.5 rounded-md transition-colors {{ !request('status') ? 'bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-400 font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                                Semua
                            </a>
                            <a href="{{ route('petugas.transaksi.index', ['status' => 'parkir']) }}"
                                class="px-3 py-1.5 rounded-md transition-colors {{ request('status') === 'parkir' ? 'bg-white dark:bg-slate-700 text-amber-600 dark:text-amber-400 font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                                Sedang Parkir ({{ $totalSedangParkir }})
                            </a>
                            <a href="{{ route('petugas.transaksi.index', ['status' => 'selesai']) }}"
                                class="px-3 py-1.5 rounded-md transition-colors {{ request('status') === 'selesai' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 font-bold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900' }}">
                                Selesai Hari Ini ({{ $totalSelesaiHariIni }})
                            </a>
                        </div>

                        <!-- Reset Filter -->
                        @if(request()->hasAny(['search', 'status', 'jenis', 'area_id', 'tanggal']))
                            <a href="{{ route('petugas.transaksi.index') }}"
                                class="text-xs font-semibold text-red-500 hover:underline flex items-center gap-1">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i> Reset Filter
                            </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <div class="relative">
                            <i data-lucide="search" class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-400"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari plat / kode tiket..."
                                class="flex h-9 w-full rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-1 pl-9 text-xs shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-blue-500 dark:text-white uppercase">
                        </div>

                        <div>
                            <select name="jenis" onchange="this.form.submit()"
                                class="flex h-9 w-full rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-1 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-blue-500 dark:text-white">
                                <option value="">Semua Jenis Kendaraan</option>
                                @foreach($tarifs as $trf)
                                    <option value="{{ $trf->jenis_kendaraan }}" {{ request('jenis') === $trf->jenis_kendaraan ? 'selected' : '' }}>
                                        {{ $trf->jenis_kendaraan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <select name="area_id" onchange="this.form.submit()"
                                class="flex h-9 w-full rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-1 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-blue-500 dark:text-white">
                                <option value="">Semua Area Parkir</option>
                                @foreach($areas as $ar)
                                    <option value="{{ $ar->id }}" {{ request('area_id') == $ar->id ? 'selected' : '' }}>
                                        {{ $ar->nama_area }} (Sisa {{ $ar->slot_tersedia }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="date" name="tanggal" value="{{ request('tanggal') }}" onchange="this.form.submit()"
                                class="flex h-9 w-full rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-1 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-blue-500 dark:text-white">
                            <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs font-semibold hover:bg-blue-500 transition-colors shrink-0">
                                Cari
                            </button>
                        </div>
                    </div>
                </form>
            </x-slot>

            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b border-slate-200 dark:border-slate-800">
                        <tr class="border-b transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Kode & Plat</th>
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Jenis & Area</th>
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Waktu & Durasi</th>
                            <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Total Biaya</th>
                            <th class="h-10 px-4 text-center align-middle font-medium text-slate-500 dark:text-slate-400">Status</th>
                            <th class="h-10 px-4 text-right align-middle font-medium text-slate-500 dark:text-slate-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        @forelse($transaksis as $t)
                            @php
                                $waktuMasukObj = \Carbon\Carbon::parse($t->waktu_masuk);
                                $biayaInfo = $t->hitungBiaya(now());
                                $durasiMenit = $t->status === 'selesai' ? ($t->durasi_menit ?? 0) : $biayaInfo['durasi_menit'];
                                $durasiTeks = floor($durasiMenit / 60) . ' Jam ' . ($durasiMenit % 60) . ' Menit';
                                $estimasiBiaya = $t->status === 'selesai' ? (float) $t->total_bayar : (float) $biayaInfo['total_bayar'];

                                $txJson = [
                                    'id' => $t->id,
                                    'kode_tiket' => $t->kode_tiket,
                                    'plat_nomor' => $t->plat_nomor,
                                    'jenis_kendaraan' => $t->jenis_kendaraan,
                                    'area_nama' => $t->areaParkir ? $t->areaParkir->nama_area : '-',
                                    'waktu_masuk' => $waktuMasukObj->translatedFormat('d M Y H:i'),
                                    'durasi_teks' => $durasiTeks,
                                    'total_bayar' => $estimasiBiaya,
                                    'url' => route('petugas.transaksi.checkout', $t->id)
                                ];
                            @endphp
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
                                <td class="p-4 align-middle text-xs">
                                    <span class="text-slate-600 dark:text-slate-300 block">Masuk: {{ $waktuMasukObj->format('d/m/y H:i') }}</span>
                                    @if($t->waktu_keluar)
                                        <span class="text-slate-500 block mt-0.5">Keluar: {{ $t->waktu_keluar->format('d/m/y H:i') }}</span>
                                        <span class="text-slate-400 block text-[11px] mt-0.5">Durasi: {{ $durasiTeks }}</span>
                                    @else
                                        <span class="text-blue-600 dark:text-blue-400 font-semibold block mt-0.5">Berjalan: {{ $durasiTeks }}</span>
                                    @endif
                                </td>
                                <td class="p-4 align-middle font-medium">
                                    @if($t->status === 'selesai')
                                        <span class="text-slate-900 dark:text-white font-bold block">Rp {{ number_format($t->total_bayar, 0, ',', '.') }}</span>
                                        <span class="text-[11px] text-slate-500">{{ $t->metode_bayar }}</span>
                                    @elseif($t->status === 'parkir')
                                        <span class="text-amber-600 dark:text-amber-400 font-bold block">Rp {{ number_format($estimasiBiaya, 0, ',', '.') }}</span>
                                        <span class="text-[10px] text-slate-400 italic">Estimasi saat ini</span>
                                    @else
                                        <span class="text-red-500 text-xs italic">Dibatalkan</span>
                                    @endif
                                </td>
                                <td class="p-4 align-middle text-center">
                                    @if($t->status === 'parkir')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                            Parkir
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
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($t->status === 'parkir')
                                            <!-- Tombol Keluar / Proses Pembayaran -->
                                            <button type="button" @click="openCheckout({{ json_encode($txJson) }})"
                                                class="inline-flex items-center justify-center rounded-md text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white h-8 px-3 transition-colors shadow-xs gap-1">
                                                <i data-lucide="credit-card" class="w-3.5 h-3.5"></i> Keluar
                                            </button>

                                            <!-- Cetak Struk Masuk -->
                                            <a href="{{ route('petugas.struk.show', ['transaksi' => $t->id, 'type' => 'masuk']) }}" target="_blank"
                                                class="inline-flex items-center justify-center rounded-md text-xs font-medium text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/50 h-8 px-2.5 transition-colors gap-1"
                                                title="Cetak Struk Masuk">
                                                <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
                                                <span class="hidden sm:inline">Struk Masuk</span>
                                            </a>

                                            <!-- Tombol Batalkan Transaksi (Salah input) -->
                                            <button type="button" @click="openCancel({{ json_encode($txJson) }})"
                                                class="inline-flex items-center justify-center rounded-md text-xs font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 hover:bg-red-100 dark:hover:bg-red-900/50 h-8 px-2.5 transition-colors"
                                                title="Batalkan transaksi salah input">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        @elseif($t->status === 'selesai')
                                            <!-- Cetak Struk Masuk -->
                                            <a href="{{ route('petugas.struk.show', ['transaksi' => $t->id, 'type' => 'masuk']) }}" target="_blank"
                                                class="inline-flex items-center justify-center rounded-md text-xs font-medium bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 h-8 px-2.5 border border-slate-200 dark:border-slate-700 gap-1 transition-colors"
                                                title="Cetak Struk Masuk">
                                                <i data-lucide="ticket" class="w-3.5 h-3.5 text-blue-500"></i>
                                                <span class="hidden sm:inline">Struk Masuk</span>
                                            </a>

                                            <!-- Cetak Struk Keluar -->
                                            <a href="{{ route('petugas.struk.show', ['transaksi' => $t->id, 'type' => 'keluar']) }}" target="_blank"
                                                class="inline-flex items-center justify-center rounded-md text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white h-8 px-2.5 gap-1 shadow-sm transition-colors"
                                                title="Cetak Struk Keluar">
                                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                                <span class="hidden sm:inline">Struk Keluar</span>
                                            </a>
                                        @else
                                            <span class="text-xs text-slate-400 italic" title="{{ $t->alasan_batal }}">Alasan: {{ \Illuminate\Support\Str::limit($t->alasan_batal, 15) }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-3 opacity-20"></i>
                                    Belum ada transaksi parkir yang cocok dengan filter.
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

        <!-- Modal Kendaraan Masuk -->
        <x-modal name="modal-masuk" title="Catat Kendaraan Masuk">
            <form action="{{ route('petugas.transaksi.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-semibold leading-none block text-slate-800 dark:text-slate-200">Plat Nomor Kendaraan</label>
                    <input type="text" name="plat_nomor" value="{{ old('plat_nomor') }}" required placeholder="Contoh: B 1234 ABC"
                        class="mt-1.5 flex h-10 w-full rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-2 text-sm font-bold tracking-wider focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white uppercase">
                    <p class="text-[11px] text-slate-400 mt-1">Huruf kecil akan otomatis diubah ke kapital dan spasi dirapikan.</p>
                </div>

                <div>
                    <label class="text-sm font-semibold leading-none mb-2 block text-slate-800 dark:text-slate-200">Jenis Kendaraan & Tarif</label>
                    <div class="grid grid-cols-2 gap-2" x-data="{ selected: '{{ old('tarif_id', $tarifs->first()->id ?? '') }}' }">
                        @foreach($tarifs as $tarif)
                            <label
                                class="flex flex-col items-center justify-between rounded-lg border-2 bg-white dark:bg-slate-900 p-3 cursor-pointer transition-colors"
                                :class="selected == '{{ $tarif->id }}' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800'">
                                <input type="radio" name="tarif_id" value="{{ $tarif->id }}" class="sr-only" x-model="selected" required>
                                <i data-lucide="{{ strtolower($tarif->jenis_kendaraan) == 'motor' ? 'bike' : (str_contains(strtolower($tarif->jenis_kendaraan), 'truk') ? 'truck' : 'car') }}"
                                    class="mb-1.5 h-5 w-5 text-blue-600 dark:text-blue-400"></i>
                                <span class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $tarif->jenis_kendaraan }}</span>
                                <span class="text-xs text-slate-500 mt-0.5">Rp {{ number_format($tarif->tarif_per_jam, 0, ',', '.') }}/jam</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold leading-none mb-1.5 block text-slate-800 dark:text-slate-200">Pilih Area Parkir</label>
                    <select name="area_parkir_id" required
                        class="flex h-10 w-full rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="">-- Pilih Area Parkir Tersedia --</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ $area->slot_tersedia <= 0 ? 'disabled' : '' }}>
                                {{ $area->nama_area }} (Sisa {{ $area->slot_tersedia }} dari {{ $area->kapasitas_total }} slot) {{ $area->slot_tersedia <= 0 ? ' - [PENUH]' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-4 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="show = false"
                        class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-950 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50 dark:hover:bg-slate-800">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex h-10 items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Simpan & Buat Tiket
                    </button>
                </div>
            </form>
        </x-modal>

        <!-- Modal Kendaraan Keluar / Checkout -->
        <x-modal name="modal-keluar" title="Proses Kendaraan Keluar & Pembayaran">
            <div class="space-y-4">
                <!-- Search Box di dalam modal jika dibuka dari header -->
                <div x-show="!checkoutData" class="space-y-3">
                    <p class="text-xs text-slate-500">Masukkan nomor plat atau kode tiket kendaraan yang ingin keluar:</p>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <i data-lucide="search" class="absolute left-3 top-3 h-4 w-4 text-slate-400"></i>
                            <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchVehicle()" placeholder="Ketik Plat (misal: B 1234 ABC) atau Tiket..."
                                class="flex h-10 w-full rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 pl-9 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white uppercase font-semibold">
                        </div>
                        <button type="button" @click="searchVehicle()" :disabled="searchLoading"
                            class="px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-semibold flex items-center gap-1.5 transition-colors">
                            <span x-show="!searchLoading">Cari</span>
                            <span x-show="searchLoading">Memeriksa...</span>
                        </button>
                    </div>

                    <template x-if="searchError">
                        <div class="p-3 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-xs flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                            <span x-text="searchError"></span>
                        </div>
                    </template>
                </div>

                <!-- Form Checkout Aktif jika checkoutData sudah terisi -->
                <template x-if="checkoutData">
                    <form :action="checkoutData.url" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded-lg border border-slate-200 dark:border-slate-700">
                                <p class="text-xs text-slate-400 mb-0.5">Plat Nomor</p>
                                <p class="font-extrabold text-lg text-slate-900 dark:text-white" x-text="checkoutData.plat_nomor"></p>
                                <p class="text-[11px] font-mono text-blue-600 dark:text-blue-400" x-text="checkoutData.kode_tiket"></p>
                            </div>
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded-lg border border-slate-200 dark:border-slate-700">
                                <p class="text-xs text-slate-400 mb-0.5">Jenis & Area</p>
                                <p class="font-bold text-sm text-slate-800 dark:text-slate-200" x-text="checkoutData.jenis_kendaraan"></p>
                                <p class="text-xs text-slate-500" x-text="checkoutData.area_nama"></p>
                            </div>
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-800/80 rounded-lg border border-slate-200 dark:border-slate-700 text-xs space-y-1">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Waktu Masuk:</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300" x-text="checkoutData.waktu_masuk"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Durasi Terhitung:</span>
                                <span class="font-bold text-blue-600 dark:text-blue-400" x-text="checkoutData.durasi_teks"></span>
                            </div>
                        </div>

                        <div class="p-4 bg-blue-50 dark:bg-blue-900/30 rounded-xl border border-blue-200 dark:border-blue-800 text-center">
                            <p class="text-xs font-bold tracking-wider text-blue-800 dark:text-blue-300 uppercase mb-1">TOTAL BIAYA PARKIR</p>
                            <p class="text-3xl font-black text-blue-900 dark:text-blue-100">
                                Rp <span x-text="new Intl.NumberFormat('id-ID').format(checkoutData.total_bayar || 0)"></span>
                            </p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold uppercase tracking-wider block text-slate-700 dark:text-slate-300">Metode Pembayaran</label>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="flex items-center justify-center p-2.5 rounded-lg border cursor-pointer text-xs font-semibold transition-all"
                                    :class="metodeBayar === 'Tunai' ? 'border-blue-600 bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'">
                                    <input type="radio" name="metode_bayar" value="Tunai" x-model="metodeBayar" class="sr-only">
                                    Tunai
                                </label>
                                <label class="flex items-center justify-center p-2.5 rounded-lg border cursor-pointer text-xs font-semibold transition-all"
                                    :class="metodeBayar === 'QRIS' ? 'border-blue-600 bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'">
                                    <input type="radio" name="metode_bayar" value="QRIS" x-model="metodeBayar" class="sr-only">
                                    QRIS
                                </label>
                                <label class="flex items-center justify-center p-2.5 rounded-lg border cursor-pointer text-xs font-semibold transition-all"
                                    :class="metodeBayar === 'Debit' ? 'border-blue-600 bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400'">
                                    <input type="radio" name="metode_bayar" value="Debit" x-model="metodeBayar" class="sr-only">
                                    Debit / EDC
                                </label>
                            </div>
                        </div>

                        <!-- Input Uang Diterima & Kembalian (Khusus Tunai) -->
                        <div x-show="metodeBayar === 'Tunai'" class="space-y-3">
                            <div>
                                <label class="text-xs font-bold uppercase tracking-wider block text-slate-700 dark:text-slate-300 mb-1">Nominal Uang Diterima (Rp)</label>
                                <input type="number" name="uang_diterima" x-model="uangDiterima" min="0" placeholder="Masukkan jumlah uang cash..."
                                    class="flex h-11 w-full rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-2 text-base font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                            </div>

                            <div class="flex justify-between items-center p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300">
                                <span class="text-xs font-semibold uppercase">Kembalian:</span>
                                <span class="font-extrabold text-lg">Rp <span x-text="new Intl.NumberFormat('id-ID').format(kembalian)"></span></span>
                            </div>
                        </div>

                        <div x-show="metodeBayar !== 'Tunai'" class="p-3 rounded-lg bg-slate-50 dark:bg-slate-800 text-xs text-slate-500">
                            Pembayaran non-tunai diverifikasi secara otomatis sebesar nilai total bayar (Rp <span x-text="new Intl.NumberFormat('id-ID').format(checkoutData.total_bayar || 0)"></span>).
                            <input type="hidden" name="uang_diterima" :value="checkoutData.total_bayar">
                        </div>

                        <div class="pt-4 flex justify-between items-center border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="checkoutData = null; searchQuery = '';"
                                class="text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                                Ganti Kendaraan
                            </button>
                            <div class="flex gap-2">
                                <button type="button" @click="show = false"
                                    class="inline-flex h-10 items-center justify-center rounded-md border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-950 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50 dark:hover:bg-slate-800">
                                    Batal
                                </button>
                                <button type="submit" :disabled="!isBayarValid"
                                    class="inline-flex h-10 items-center justify-center rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                    Konfirmasi & Cetak Struk
                                </button>
                            </div>
                        </div>
                    </form>
                </template>
            </div>
        </x-modal>

        <!-- Modal Batalkan Transaksi Salah Input -->
        <x-modal name="modal-batal" title="Batalkan Transaksi Parkir">
            <template x-if="cancelData">
                <form :action="'{{ url('/petugas/transaksi') }}/' + cancelData.id + '/batal'" method="POST" class="space-y-4">
                    @csrf

                    <div class="p-3 bg-red-50 dark:bg-red-950/40 rounded-lg border border-red-200 dark:border-red-900/50 text-xs text-red-800 dark:text-red-300">
                        <div class="font-bold mb-1">Perhatian! Tindakan ini akan membatalkan transaksi berikut:</div>
                        <div>Plat: <span class="font-mono font-bold" x-text="cancelData.plat_nomor"></span> &bull; Tiket: <span class="font-mono" x-text="cancelData.kode_tiket"></span></div>
                        <div class="text-[11px] text-red-600 dark:text-red-400 mt-1">Slot area parkir akan otomatis dilepaskan kembali dan pembatalan akan tercatat di log aktivitas.</div>
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider block text-slate-700 dark:text-slate-300 mb-1">
                            Alasan Pembatalan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="alasan_batal" x-model="alasanBatal" required rows="3" placeholder="Contoh: Salah ketik nomor plat oleh petugas / salah pilih jenis kendaraan..."
                            class="w-full rounded-md border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 dark:text-white"></textarea>
                    </div>

                    <div class="pt-3 flex justify-end gap-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="show = false"
                            class="inline-flex h-9 items-center justify-center rounded-md border border-slate-200 bg-white px-4 text-xs font-medium text-slate-950 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-50 dark:hover:bg-slate-800">
                            Tutup
                        </button>
                        <button type="submit" :disabled="!alasanBatal || alasanBatal.trim().length < 5"
                            class="inline-flex h-9 items-center justify-center rounded-md bg-red-600 px-4 text-xs font-semibold text-white hover:bg-red-700 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            Konfirmasi Batalkan
                        </button>
                    </div>
                </form>
            </template>
        </x-modal>

    </div>
@endsection