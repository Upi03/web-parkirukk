@extends('layouts.dashboard')

@section('title', 'Manajemen User - SmarPARK')

@section('content')
<div class="flex flex-col gap-6" x-data="{
    showCreateModal: false,
    showEditModal: false,
    showDeleteModal: false,
    currentUser: { id: '', name: '', email: '', role: 'petugas', is_active: true },
    deleteUrl: '',
    openEdit(user) {
        this.currentUser = { ...user, password: '' };
        this.showEditModal = true;
    },
    openDelete(url, name) {
        this.deleteUrl = url;
        this.currentUser.name = name;
        this.showDeleteModal = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen User</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola akun Admin, Petugas, dan Owner serta hak akses sistem.</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah User
        </button>
    </div>

    <!-- Table Card -->
    <x-card>
        <!-- Toolbar (Search & Filter) -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
            <div class="relative w-full sm:w-72">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari user (nama, email)..." 
                    class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
            </div>
            <div class="flex items-center gap-2">
                <select name="role" onchange="this.form.submit()" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                    <option value="">Semua Role</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="petugas" {{ request('role') === 'petugas' ? 'selected' : '' }}>Petugas</option>
                    <option value="owner" {{ request('role') === 'owner' ? 'selected' : '' }}>Owner</option>
                </select>
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-white">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @if(request('search') || request('role') || request('status'))
                    <a href="{{ route('admin.users.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 rounded-lg text-sm font-medium transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- Table -->
        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead class="[&_tr]:border-b border-slate-200 dark:border-slate-800">
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20 transition-colors">
                        <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Nama User</th>
                        <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Email</th>
                        <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Role</th>
                        <th class="h-10 px-4 text-left align-middle font-medium text-slate-500 dark:text-slate-400">Status</th>
                        <th class="h-10 px-4 text-right align-middle font-medium text-slate-500 dark:text-slate-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    @forelse($users as $user)
                    <tr class="border-b border-slate-200 dark:border-slate-800 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="p-4 align-middle font-medium">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="text-slate-900 dark:text-white font-medium">{{ $user->name }}</span>
                                    @if($user->id === auth()->id())
                                        <span class="ml-1 text-[10px] bg-blue-50 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 px-1.5 py-0.5 rounded font-semibold">(Anda)</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-4 align-middle text-slate-600 dark:text-slate-400">{{ $user->email }}</td>
                        <td class="p-4 align-middle">
                            @if($user->role === 'admin')
                                <span class="px-2.5 py-0.5 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 text-xs rounded-full font-medium capitalize">
                                    {{ $user->role }}
                                </span>
                            @elseif($user->role === 'petugas')
                                <span class="px-2.5 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 text-xs rounded-full font-medium capitalize">
                                    {{ $user->role }}
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 text-xs rounded-full font-medium capitalize">
                                    {{ $user->role }}
                                </span>
                            @endif
                        </td>
                        <td class="p-4 align-middle">
                            <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                    @if($user->id === auth()->id()) disabled title="Tidak dapat mengubah status akun sendiri" @endif
                                    class="px-2 py-1 text-xs rounded-full flex items-center gap-1.5 transition-opacity {{ $user->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 hover:opacity-80' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 hover:opacity-80' }} {{ $user->id === auth()->id() ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="p-4 align-middle text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" @click="openEdit({{ json_encode($user) }})" 
                                    class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors" title="Edit User">
                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                </button>

                                @if($user->id !== auth()->id())
                                <button type="button" @click="openDelete('{{ route('admin.users.destroy', $user->id) }}', '{{ $user->name }}')" 
                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Hapus User">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                                @else
                                <span class="p-2 text-slate-300 dark:text-slate-600 cursor-not-allowed" title="Tidak dapat menghapus akun sendiri">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">
                            <i data-lucide="users" class="w-8 h-8 mx-auto mb-3 opacity-20"></i>
                            Tidak ada data user yang sesuai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Real -->
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </x-card>

    <!-- Modal Tambah User -->
    <div x-show="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.outside="showCreateModal = false" class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-semibold text-slate-900 dark:text-white">Tambah User Baru</h3>
                <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Contoh: Ahmad Fauzi" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Email / Username</label>
                    <input type="email" name="email" required placeholder="user@smarpark.local" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="Minimal 6 karakter" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Role Akun</label>
                    <select name="role" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                        <option value="petugas">Petugas</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="is_active_create" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="is_active_create" class="text-sm font-medium text-slate-700 dark:text-slate-300">Akun Langsung Aktif</label>
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">Simpan User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.outside="showEditModal = false" class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-semibold text-slate-900 dark:text-white">Edit User</h3>
                <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" :action="'{{ url('admin/users') }}/' + currentUser.id" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="currentUser.name" required 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Email / Username</label>
                    <input type="email" name="email" x-model="currentUser.email" required 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                        Password Baru <span class="text-slate-400 font-normal lowercase">(kosongkan jika tidak diubah)</span>
                    </label>
                    <input type="password" name="password" placeholder="Minimal 6 karakter" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Role Akun</label>
                    <select name="role" x-model="currentUser.role" required class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                        <option value="petugas">Petugas</option>
                        <option value="admin">Admin</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_active" id="is_active_edit" value="1" :checked="currentUser.is_active" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="is_active_edit" class="text-sm font-medium text-slate-700 dark:text-slate-300">Status Akun Aktif</label>
                </div>
                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">Perbarui User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-cloak>
        <div @click.outside="showDeleteModal = false" class="bg-white dark:bg-slate-800 rounded-xl shadow-xl w-full max-w-sm overflow-hidden border border-slate-200 dark:border-slate-700 p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 mx-auto flex items-center justify-center mb-4">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-2">Hapus Akun User?</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                Apakah Anda yakin ingin menghapus user <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="currentUser.name"></span>? Tindakan ini tidak dapat dibatalkan.
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
