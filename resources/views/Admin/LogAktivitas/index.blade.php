@extends('layouts.dashboard')

@section('title', 'Log Aktivitas - SmarPARK')

@section('content')
<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Log Aktivitas</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Audit trail dan riwayat segala aksi yang terjadi di dalam sistem SmarPARK.</p>
        </div>
    </div>

    <x-card>
        <!-- Toolbar & Filter -->
        <form method="GET" action="{{ route('admin.log.index') }}" class="flex flex-col gap-4 mb-6">
            <div class="flex flex-col md:flex-row gap-3">
                <div class="relative flex-1">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari log (aksi, keterangan, nama user)..." 
                        class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <select name="user_id" onchange="this.form.submit()" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="">Semua User</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>

                    <select name="action_type" onchange="this.form.submit()" class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:text-white">
                        <option value="">Semua Aksi</option>
                        <option value="Tambah" {{ request('action_type') === 'Tambah' ? 'selected' : '' }}>Tambah Data</option>
                        <option value="Edit" {{ request('action_type') === 'Edit' ? 'selected' : '' }}>Edit Data</option>
                        <option value="Hapus" {{ request('action_type') === 'Hapus' ? 'selected' : '' }}>Hapus Data</option>
                        <option value="Kendaraan" {{ request('action_type') === 'Kendaraan' ? 'selected' : '' }}>Transaksi Kendaraan</option>
                        <option value="Login" {{ request('action_type') === 'Login' ? 'selected' : '' }}>Login</option>
                        <option value="Logout" {{ request('action_type') === 'Logout' ? 'selected' : '' }}>Logout</option>
                    </select>

                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" onchange="this.form.submit()" 
                        class="px-3 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 dark:text-white">

                    @if(request('search') || request('user_id') || request('action_type') || request('tanggal'))
                        <a href="{{ route('admin.log.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-200 rounded-lg text-sm font-medium transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="relative w-full overflow-auto">
            <table class="w-full caption-bottom text-sm">
                <thead class="[&_tr]:border-b border-slate-200 dark:border-slate-800">
                    <tr class="bg-slate-50/50 dark:bg-slate-800/20 transition-colors">
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Waktu</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">User Pelaksana</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Role</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Tipe Aksi</th>
                        <th class="h-10 px-4 text-left font-medium text-slate-500 dark:text-slate-400">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="[&_tr:last-child]:border-0">
                    @forelse($logs as $log)
                    <tr class="border-b border-slate-200 dark:border-slate-800 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <td class="p-4 align-middle whitespace-nowrap text-xs text-slate-500 dark:text-slate-400">
                            <div class="font-medium text-slate-700 dark:text-slate-300">{{ $log->created_at->diffForHumans() }}</div>
                            <div>{{ $log->created_at->translatedFormat('d M Y, H:i:s') }}</div>
                        </td>
                        <td class="p-4 align-middle font-medium whitespace-nowrap text-slate-900 dark:text-white">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center font-bold text-xs text-slate-600 dark:text-slate-300">
                                    {{ strtoupper(substr(optional($log->user)->name ?? 'S', 0, 1)) }}
                                </div>
                                <div>
                                    <div>{{ optional($log->user)->name ?? 'Sistem' }}</div>
                                    @if($log->user)
                                        <div class="text-[11px] text-slate-400 font-normal">{{ $log->user->email }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-4 align-middle whitespace-nowrap">
                            @if(optional($log->user)->role === 'admin')
                                <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 font-medium capitalize">
                                    Admin
                                </span>
                            @elseif(optional($log->user)->role === 'petugas')
                                <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 font-medium capitalize">
                                    Petugas
                                </span>
                            @elseif(optional($log->user)->role === 'owner')
                                <span class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300 font-medium capitalize">
                                    Owner
                                </span>
                            @else
                                <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 font-medium">
                                    Sistem
                                </span>
                            @endif
                        </td>
                        <td class="p-4 align-middle whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $log->action_badge_class }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="p-4 align-middle text-slate-600 dark:text-slate-300 text-xs">
                            {{ $log->description }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">
                            <i data-lucide="history" class="w-8 h-8 mx-auto mb-3 opacity-20"></i>
                            Tidak ada data log aktivitas yang cocok dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </x-card>
</div>
@endsection
