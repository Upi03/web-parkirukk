<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingRequest;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
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
        ];

        return view('Admin.Pengaturan.index', compact('settings'));
    }

    public function update(UpdateSettingRequest $request)
    {
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        ActivityLog::record(
            'Ubah Pengaturan',
            'Admin ' . auth()->user()->name . ' memperbarui pengaturan sistem parkir.'
        );

        return redirect()->route('admin.pengaturan.index')->with('success', 'Pengaturan sistem berhasil disimpan.');
    }
}
