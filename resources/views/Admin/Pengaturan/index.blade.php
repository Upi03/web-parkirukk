@extends('layouts.dashboard')

@section('title', 'Pengaturan Sistem - SmarPARK')

@section('content')
<div class="flex flex-col gap-6 max-w-4xl">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Pengaturan Sistem</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Konfigurasi parameter operasional dan identitas sistem parkir.</p>
    </div>

    <x-card>
        <x-slot name="header">
            <h3 class="font-semibold text-slate-900 dark:text-white">Parameter Aplikasi & Informasi Lokasi</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pengaturan ini akan digunakan pada kop struk, nama sistem, dan kebijakan operasional.</p>
        </x-slot>

        <form method="POST" action="{{ route('admin.pengaturan.update') }}" class="p-6 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nama Aplikasi</label>
                    <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nama Pengelola / Perusahaan</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Alamat Lokasi Parkir</label>
                    <input type="text" name="company_address" value="{{ old('company_address', $settings['company_address']) }}" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nomor Kontak / WhatsApp</label>
                    <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                        Waktu Toleransi Gratis (Menit)
                    </label>
                    <input type="number" name="tolerance_minutes" value="{{ old('tolerance_minutes', $settings['tolerance_minutes']) }}" min="0" max="120" required 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Durasi masuk sebelum dikenakan tarif minimum.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">
                        Pesan Footer Struk Parkir
                    </label>
                    <input type="text" name="footer_text" value="{{ old('footer_text', $settings['footer_text']) }}" 
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none dark:text-white">
                    <p class="text-[11px] text-slate-400 mt-1">Teks catatan di bagian bawah struk pembayaran.</p>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors shadow-sm">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </x-card>
</div>
@endsection
