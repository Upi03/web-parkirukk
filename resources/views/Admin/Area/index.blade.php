@extends('layouts.dashboard')

@section('title', 'Area Parkir - SmarPARK')

@section('content')
<div class="flex flex-col gap-6" x-data="{
    showCreateModal: false,
    showEditModal: false,
    showDeleteModal: false,
    currentArea: { id: '', nama_area: '', kapasitas_total: '', keterangan: '', status: 'aktif', slot_terisi: 0 },
    deleteUrl: '',
    openEdit(area) {
        this.currentArea = {
            id: area.id,
            nama_area: area.nama_area,
            kapasitas_total: area.kapasitas_total,
            keterangan: area.keterangan ?? '',
            status: area.status,
            slot_terisi: area.slot_terisi
        };
        this.showEditModal = true;
    },
    openDelete(url, nama) {
        this.deleteUrl = url;
        this.currentArea.nama_area = nama;
        this.showDeleteModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Area Parkir</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Manajemen area/blok parkir, kapasitas slot, dan pemantauan ketersediaan real-time.</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Area
        </button>
    </div>

    <x-card>
        <!-- Toolbar -->
        <form method="GET" action="{{ route('admin.area.index') }}" class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
            <div class="relative w-full sm:w-72">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama area..." 
                    class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
            </div>
            <div class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:text-white">
                    <option value="">Semua Status Operasional</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.area.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 rounded-lg text-sm font-medium transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead class="[&_tr]:border-b border-slate-200 dark:border-slate-800">
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20 transition-colors">
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Nama Area</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Kapasitas Slot</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Okupansi Slot</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Status</th>
                        <th class="h-10 px-4 text-right font-medium text-slate-500 dark:text-slate-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    @forelse($areas as $area)
                    @php
                        $terisi = $area->slot_terisi;
                        $kapasitas = max(1, $area->kapasitas_total);
                        $pct = round(($terisi / $kapasitas) * 100);
                        $isPenuh = $terisi >= $area->kapasitas_total;
                    @endphp
                    <tr class="border-b border-slate-200 dark:border-slate-800 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="p-4 align-middle">
                            <div class="font-medium text-slate-900 dark:text-white">{{ $area->nama_area }}</div>
                            @if($area->keterangan)
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $area->keterangan }}</div>
                            @endif
                        </td>
                        <td class="p-4 align-middle font-medium text-slate-700 dark:text-slate-300">
                            {{ $area->kapasitas_total }} Kendaraan
                        </td>
                        <td class="p-4 align-middle" style="min-width: 180px;">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="font-medium text-slate-700 dark:text-slate-300">Terisi {{ $terisi }} slot</span>
                                <span class="text-slate-500">Sisa {{ max(0, $area->kapasitas_total - $terisi) }}</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-300 {{ $pct >= 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-blue-600') }}" 
                                    style="width: {{ min(100, $pct) }}%"></div>
                            </div>
                        </td>
                        <td class="p-4 align-middle">
                            <div class="flex flex-col gap-1 items-start">
                                @if($area->status === 'nonaktif')
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 font-medium">Nonaktif</span>
                                @elseif($isPenuh)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400 font-medium">Penuh</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 font-medium">Tersedia</span>
                                @endif
                            </div>
                        </td>
                        <td class="p-4 align-middle text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" @click="openEdit({{ json_encode(array_merge($area->toArray(), ['slot_terisi' => $area->slot_terisi])) }})" 
                                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Edit Area">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <button type="button" @click="openDelete('{{ route('admin.area.destroy', $area->id) }}', '{{ $area->nama_area }}')" 
                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Hapus Area">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">
                            <i data-lucide="map-pin" class="w-8 h-8 mx-auto mb-3 opacity-20"></i>
                            Belum ada area parkir yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $areas->links() }}
        </div>
    </x-card>

    <!-- Modal Tambah Area -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.outside="showCreateModal = false" class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-semibold text-slate-900 dark:text-white">Tambah Area Parkir</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.area.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nama Area / Blok</label>
                    <input type="text" name="nama_area" required placeholder="Contoh: Gedung A - Lt 1" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Kapasitas Slot</label>
                    <input type="number" name="kapasitas_total" required min="1" placeholder="Contoh: 50" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Keterangan / Lokasi</label>
                    <textarea name="keterangan" rows="2" placeholder="Contoh: Khusus kendaraan roda 4 dan tamu VIP" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Status Operasional</label>
                    <select name="status" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                        <option value="aktif">Aktif (Dapat digunakan)</option>
                        <option value="nonaktif">Nonaktif (Dalam perbaikan / ditutup)</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">Simpan Area</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Area -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.outside="showEditModal = false" class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-semibold text-slate-900 dark:text-white">Edit Area Parkir</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" :action="'{{ url('admin/area') }}/' + currentArea.id" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nama Area / Blok</label>
                    <input type="text" name="nama_area" x-model="currentArea.nama_area" required 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                        Kapasitas Slot 
                        <span class="text-xs text-blue-600 dark:text-blue-400 font-normal" x-show="currentArea.slot_terisi > 0" x-text="'(Minimal ' + currentArea.slot_terisi + ' karena sedang terisi)'"></span>
                    </label>
                    <input type="number" name="kapasitas_total" x-model="currentArea.kapasitas_total" required :min="currentArea.slot_terisi || 1" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Keterangan / Lokasi</label>
                    <textarea name="keterangan" x-model="currentArea.keterangan" rows="2" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Status Operasional</label>
                    <select name="status" x-model="currentArea.status" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                        <option value="aktif">Aktif (Dapat digunakan)</option>
                        <option value="nonaktif">Nonaktif (Dalam perbaikan / ditutup)</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">Perbarui Area</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Area -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.outside="showDeleteModal = false" class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-slate-200 dark:border-slate-700 p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 mx-auto flex items-center justify-center mb-4">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-2">Hapus Area Parkir?</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                Apakah Anda yakin ingin menghapus area <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="currentArea.nama_area"></span>? Area tidak dapat dihapus jika masih ada kendaraan aktif atau memiliki riwayat transaksi.
            </p>
            <form method="POST" :action="deleteUrl" class="flex gap-3 justify-center">
                @csrf
                @method('DELETE')
                <button type="button" @click="showDeleteModal = false" class="w-1/2 px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</button>
                <button type="submit" class="w-1/2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">Hapus</button>
            </form>
        </div>
    </div>
</div>
@endsection
