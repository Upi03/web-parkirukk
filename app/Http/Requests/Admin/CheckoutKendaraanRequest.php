<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutKendaraanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'uang_diterima' => 'required|numeric|min:0',
            'metode_bayar' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'uang_diterima.required' => 'Jumlah uang yang diterima wajib diisi.',
            'uang_diterima.numeric' => 'Jumlah uang harus berupa angka.',
            'uang_diterima.min' => 'Jumlah uang tidak boleh bernilai negatif.',
            'metode_bayar.required' => 'Metode pembayaran wajib dipilih.',
        ];
    }
}
