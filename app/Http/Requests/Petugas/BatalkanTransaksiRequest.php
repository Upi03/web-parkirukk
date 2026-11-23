<?php

namespace App\Http\Requests\Petugas;

use Illuminate\Foundation\Http\FormRequest;

class BatalkanTransaksiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'petugas';
    }

    public function rules(): array
    {
        return [
            'alasan_batal' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'alasan_batal.required' => 'Alasan pembatalan transaksi wajib diisi.',
            'alasan_batal.min' => 'Alasan pembatalan minimal 5 karakter.',
            'alasan_batal.max' => 'Alasan pembatalan maksimal 500 karakter.',
        ];
    }
}
