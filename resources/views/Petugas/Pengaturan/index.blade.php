<?php
// resources/views/Petugas/Pengaturan/index.blade.php
?>
@extends('layouts.dashboard')

@section('title', 'Pengaturan Sistem - SmarPARK')

@section('content')
<div class="flex flex-col gap-6 max-w-4xl">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Pengaturan Operasional Petugas</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Konfigurasi preferensi cetak struk dan informasi parameter sistem aktif.</p>
    </div>

    <!-- Preferensi Cetak Struk Petugas -->
    <x-card>
        <x-slot name="header">
            <h3 class="font-semibold text-slate-900 dark:text-white">Preferensi Perangkat Cetak Struk</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Pilih ukuran lebar kertas default yang sesuai dengan printer thermal di pos Anda.</p>
        </x-slot>

        <form method="POST" action="{{ route('petugas.pengaturan.update') }}" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Ukuran Kertas Thermal Default</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all {{ $settings['receipt_paper_size'] === '80mm' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <input type="radio" name="receipt_paper_size" value="80mm" {{ $settings['receipt_paper_size'] === '80mm' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block text-sm font-bold text-slate-800 dark:text-slate-200">80 mm (Standar POS)</span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">Printer desktop kasir / printer USB thermal 80mm.</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 border rounded-xl cursor-pointer transition-all {{ $settings['receipt_paper_size'] === '58mm' ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <input type="radio" name="receipt_paper_size" value="58mm" {{ $settings['receipt_paper_size'] === '58mm' ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="block text-sm font-bold text-slate-800 dark:text-slate-200">58 mm (Printer Portabel / Mini)</span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400 mt-0.5">Printer Bluetooth portable / mobile thermal 58mm.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex justify-end pt-3">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                    Simpan Preferensi
                </button>
            </div>
        </form>
    </x-card>

    <!-- Informasi Parameter Sistem (Read-Only untuk Petugas) -->
    <x-card>
        <x-slot name="header">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-white">Parameter Sistem Aktif</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Informasi lokasi, tarif dasar, dan identitas perusahaan yang dikonfigurasi Administrator.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                    Read Only (Admin)
                </span>
            </div>
        </x-slot>

        <div class="p-6 space-y-4 text-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Nama Sistem</span>
                    <span class="font-bold text-slate-900 dark:text-white mt-1 block">{{ $settings['app_name'] }}</span>
                </div>
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Pengelola / Perusahaan</span>
                    <span class="font-bold text-slate-900 dark:text-white mt-1 block">{{ $settings['company_name'] }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Alamat Lokasi Parkir</span>
                    <span class="font-medium text-slate-700 dark:text-slate-300 mt-1 block">{{ $settings['company_address'] }}</span>
                </div>
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Nomor Kontak Pos</span>
                    <span class="font-medium text-slate-700 dark:text-slate-300 mt-1 block">{{ $settings['company_phone'] }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Toleransi Gratis Drop-Off</span>
                    <span class="font-bold text-blue-600 dark:text-blue-400 mt-1 block">{{ $settings['tolerance_minutes'] }} Menit Pertama</span>
                </div>
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                    <span class="block text-xs font-semibold text-slate-400 uppercase">Pesan Footer Struk</span>
                    <span class="font-medium text-slate-700 dark:text-slate-300 mt-1 block italic">"{{ $settings['footer_text'] }}"</span>
                </div>
            </div>
        </div>
    </x-card>
</div>
@endsection
