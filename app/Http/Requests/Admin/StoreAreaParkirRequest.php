<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAreaParkirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'nama_area' => 'required|string|max:100|unique:area_parkirs,nama_area',
            'kapasitas_total' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:500',
            'status' => 'required|in:aktif,nonaktif',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_area.required' => 'Nama area parkir wajib diisi.',
            'nama_area.unique' => 'Nama area parkir sudah digunakan.',
            'kapasitas_total.required' => 'Kapasitas total wajib diisi.',
            'kapasitas_total.integer' => 'Kapasitas total harus berupa bilangan bulat.',
            'kapasitas_total.min' => 'Kapasitas total minimal 1 slot.',
            'status.required' => 'Status area wajib dipilih.',
            'status.in' => 'Status area tidak valid.',
        ];
    }
}
