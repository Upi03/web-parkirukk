<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    public function index()
    {
        $settings = [
            'app_name' => Setting::get('app_name', 'SmarPARK'),
            'company_name' => Setting::get('company_name', 'PT Solusi Parkir Pintar'),
            'company_address' => Setting::get('company_address', 'Jl. Merdeka No. 45, Jakarta'),
            'company_phone' => Setting::get('company_phone', '0812-3456-7890'),
            'tolerance_minutes' => Setting::get('tolerance_minutes', 10),
            'footer_text' => Setting::get('footer_text', 'SmarPARK - Sistem Manajemen Parkir Modern & Terintegrasi'),
            'receipt_paper_size' => Setting::get('receipt_paper_size', '80mm'),
        ];

        return view('Petugas.Pengaturan.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'receipt_paper_size' => 'required|in:58mm,80mm',
        ], [
            'receipt_paper_size.required' => 'Ukuran kertas struk wajib dipilih.',
            'receipt_paper_size.in' => 'Pilihan ukuran kertas harus 58mm atau 80mm.',
        ]);

        Setting::set('receipt_paper_size', $request->receipt_paper_size, 'petugas');

        ActivityLog::record(
            'Ubah Pengaturan Struk',
            "Petugas " . auth()->user()->name . " mengubah default ukuran kertas struk menjadi {$request->receipt_paper_size}.",
            auth()->id()
        );

        return redirect()->route('petugas.pengaturan.index')->with('success', 'Preferensi cetak struk berhasil disimpan.');
    }
}
