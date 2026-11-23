<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'app_name' => 'required|string|max:100',
            'company_name' => 'nullable|string|max:100',
            'company_address' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:30',
            'tolerance_minutes' => 'required|integer|min:0|max:120',
            'footer_text' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'app_name.required' => 'Nama aplikasi wajib diisi.',
            'tolerance_minutes.required' => 'Waktu toleransi gratis (menit) wajib diisi.',
            'tolerance_minutes.integer' => 'Waktu toleransi harus berupa angka bulat.',
            'tolerance_minutes.min' => 'Waktu toleransi tidak boleh kurang dari 0 menit.',
        ];
    }
}
