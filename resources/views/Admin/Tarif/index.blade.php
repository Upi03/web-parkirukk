@extends('layouts.dashboard')

@section('title', 'Tarif Parkir - SmarPARK')

@section('content')
<div class="flex flex-col gap-6" x-data="{
    showCreateModal: false,
    showEditModal: false,
    showDeleteModal: false,
    currentTarif: { id: '', jenis_kendaraan: '', tarif_per_jam: '', tarif_pertama: '', tarif_maksimal: '' },
    deleteUrl: '',
    openEdit(tarif) {
        this.currentTarif = {
            id: tarif.id,
            jenis_kendaraan: tarif.jenis_kendaraan,
            tarif_per_jam: tarif.tarif_per_jam,
            tarif_pertama: tarif.tarif_pertama ?? '',
            tarif_maksimal: tarif.tarif_maksimal ?? ''
        };
        this.showEditModal = true;
    },
    openDelete(url, jenis) {
        this.deleteUrl = url;
        this.currentTarif.jenis_kendaraan = jenis;
        this.showDeleteModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Tarif Parkir</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola tarif parkir per jam, jam pertama, dan tarif maksimal untuk setiap jenis kendaraan.</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Tarif
        </button>
    </div>

    <x-card>
        <!-- Toolbar -->
        <form method="GET" action="{{ route('admin.tarif.index') }}" class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
            <div class="relative w-full sm:w-72">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari jenis kendaraan..." 
                    class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
            </div>
            @if(request('search'))
                <a href="{{ route('admin.tarif.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 rounded-lg text-sm font-medium transition-colors w-max">
                    Reset Pencarian
                </a>
            @endif
        </form>

        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead class="[&_tr]:border-b border-slate-200 dark:border-slate-800">
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20 transition-colors">
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Jenis Kendaraan</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Tarif 1 Jam Pertama</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Tarif Per Jam Berikutnya</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Tarif Maksimal (24 Jam)</th>
                        <th class="h-10 px-4 text-right font-medium text-slate-500 dark:text-slate-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    @forelse($tarifs as $tarif)
                    <tr class="border-b border-slate-200 dark:border-slate-800 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="p-4 align-middle font-medium text-slate-900 dark:text-white">
                            <div class="flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                    <i data-lucide="car" class="w-4 h-4"></i>
                                </span>
                                {{ $tarif->jenis_kendaraan }}
                            </div>
                        </td>
                        <td class="p-4 align-middle text-slate-600 dark:text-slate-300">
                            {{ $tarif->tarif_pertama ? 'Rp ' . number_format($tarif->tarif_pertama, 0, ',', '.') : 'Rp ' . number_format($tarif->tarif_per_jam, 0, ',', '.') }}
                        </td>
                        <td class="p-4 align-middle font-medium text-slate-900 dark:text-white">
                            Rp {{ number_format($tarif->tarif_per_jam, 0, ',', '.') }}
                        </td>
                        <td class="p-4 align-middle text-slate-600 dark:text-slate-300">
                            {{ $tarif->tarif_maksimal ? 'Rp ' . number_format($tarif->tarif_maksimal, 0, ',', '.') : 'Tidak ada batas' }}
                        </td>
                        <td class="p-4 align-middle text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" @click="openEdit({{ json_encode($tarif) }})" 
                                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Edit Tarif">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>
                                <button type="button" @click="openDelete('{{ route('admin.tarif.destroy', $tarif->id) }}', '{{ $tarif->jenis_kendaraan }}')" 
                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Hapus Tarif">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">
                            <i data-lucide="banknote" class="w-8 h-8 mx-auto mb-3 opacity-20"></i>
                            Belum ada tarif parkir yang ditambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $tarifs->links() }}
        </div>
    </x-card>

    <!-- Modal Tambah Tarif -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.outside="showCreateModal = false" class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-semibold text-slate-900 dark:text-white">Tambah Tarif Parkir</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.tarif.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Jenis Kendaraan</label>
                    <input type="text" name="jenis_kendaraan" required placeholder="Contoh: Motor, Mobil, Truk" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Tarif Per Jam (Rp)</label>
                    <input type="number" name="tarif_per_jam" required min="0" placeholder="Contoh: 3000" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                        Tarif 1 Jam Pertama (Rp) <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <input type="number" name="tarif_pertama" min="0" placeholder="Kosongkan jika sama dengan tarif per jam" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                        Tarif Maksimal Per Hari (Rp) <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <input type="number" name="tarif_maksimal" min="0" placeholder="Kosongkan jika tidak ada batas" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">Simpan Tarif</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Tarif -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.outside="showEditModal = false" class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-semibold text-slate-900 dark:text-white">Edit Tarif Parkir</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" :action="'{{ url('admin/tarif') }}/' + currentTarif.id" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Jenis Kendaraan</label>
                    <input type="text" name="jenis_kendaraan" x-model="currentTarif.jenis_kendaraan" required 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Tarif Per Jam (Rp)</label>
                    <input type="number" name="tarif_per_jam" x-model="currentTarif.tarif_per_jam" required min="0" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                        Tarif 1 Jam Pertama (Rp) <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <input type="number" name="tarif_pertama" x-model="currentTarif.tarif_pertama" min="0" placeholder="Kosongkan jika sama" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                        Tarif Maksimal Per Hari (Rp) <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <input type="number" name="tarif_maksimal" x-model="currentTarif.tarif_maksimal" min="0" placeholder="Kosongkan jika tidak ada batas" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">Perbarui Tarif</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Hapus Tarif -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.outside="showDeleteModal = false" class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-slate-200 dark:border-slate-700 p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 mx-auto flex items-center justify-center mb-4">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-2">Hapus Tarif?</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                Apakah Anda yakin ingin menghapus tarif untuk kendaraan <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="currentTarif.jenis_kendaraan"></span>?
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
