<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKendaraanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'plat_nomor' => 'required|string|max:20',
            'tarif_id' => 'required|exists:tarifs,id',
            'area_parkir_id' => 'nullable|exists:area_parkirs,id',
            'status' => 'required|in:parkir,selesai',
        ];
    }

    public function messages(): array
    {
        return [
            'plat_nomor.required' => 'Plat nomor kendaraan wajib diisi.',
            'tarif_id.required' => 'Jenis kendaraan / tarif wajib dipilih.',
            'tarif_id.exists' => 'Tarif yang dipilih tidak valid.',
            'area_parkir_id.exists' => 'Area parkir yang dipilih tidak valid.',
            'status.required' => 'Status parkir wajib dipilih.',
            'status.in' => 'Status parkir tidak valid.',
        ];
    }
}
