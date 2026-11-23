@extends('layouts.dashboard')

@section('title', 'Profil Saya - SmarPARK')

@section('content')
<div class="flex flex-col gap-6 max-w-4xl">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Profil Pengguna</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Perbarui identitas akun dan kata sandi Anda.</p>
    </div>

    <!-- Informasi Profil -->
    <x-card>
        <x-slot name="header">
            <h3 class="font-semibold text-slate-900 dark:text-white">Informasi Akun</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Perbarui nama lengkap dan alamat email yang digunakan untuk login.</p>
        </x-slot>

        <form method="POST" action="{{ route('admin.profil.update') }}" class="p-6 space-y-4">
            @csrf
            @method('PATCH')

            <div class="flex items-center gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-xl">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h4 class="font-semibold text-slate-900 dark:text-white">{{ $user->name }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }} &bull; Role: <span class="capitalize font-semibold text-blue-600 dark:text-blue-400">{{ $user->role }}</span></p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-card>

    <!-- Ganti Kata Sandi -->
    <x-card>
        <x-slot name="header">
            <h3 class="font-semibold text-slate-900 dark:text-white">Ganti Kata Sandi</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pastikan menggunakan kombinasi kata sandi yang aman dan sulit ditebak.</p>
        </x-slot>

        <form method="POST" action="{{ route('admin.profil.password') }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Kata Sandi Saat Ini</label>
                <input type="password" name="current_password" required placeholder="Masukkan kata sandi saat ini" 
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Kata Sandi Baru</label>
                <input type="password" name="password" required placeholder="Minimal 6 karakter" 
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Konfirmasi Kata Sandi Baru</label>
                <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi baru" 
                    class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Ubah Kata Sandi
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
