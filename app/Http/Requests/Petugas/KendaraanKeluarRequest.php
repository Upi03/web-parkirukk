<?php

namespace App\Http\Requests\Petugas;

use Illuminate\Foundation\Http\FormRequest;

class KendaraanKeluarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'petugas';
    }

    public function rules(): array
    {
        return [
            'metode_bayar' => ['required', 'string', 'in:Tunai,QRIS,Debit,Transfer'],
            'uang_diterima' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'metode_bayar.required' => 'Metode pembayaran wajib dipilih.',
            'metode_bayar.in' => 'Metode pembayaran yang dipilih tidak valid.',
            'uang_diterima.required' => 'Nominal uang diterima wajib diisi.',
            'uang_diterima.numeric' => 'Nominal uang diterima harus berupa angka.',
            'uang_diterima.min' => 'Nominal uang diterima tidak boleh kurang dari 0.',
        ];
    }
}
