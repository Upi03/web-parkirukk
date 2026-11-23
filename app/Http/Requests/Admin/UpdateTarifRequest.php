<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTarifRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        $tarifId = $this->route('tarif') instanceof \App\Models\Tarif 
            ? $this->route('tarif')->id 
            : $this->route('tarif');

        return [
            'jenis_kendaraan' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tarifs')->ignore($tarifId),
            ],
            'tarif_per_jam' => 'required|numeric|min:0',
            'tarif_pertama' => 'nullable|numeric|min:0',
            'tarif_maksimal' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_kendaraan.required' => 'Jenis kendaraan wajib diisi.',
            'jenis_kendaraan.unique' => 'Jenis kendaraan ini sudah ada di daftar tarif.',
            'tarif_per_jam.required' => 'Tarif per jam wajib diisi.',
            'tarif_per_jam.numeric' => 'Tarif per jam harus berupa angka.',
            'tarif_per_jam.min' => 'Tarif per jam tidak boleh negatif.',
            'tarif_pertama.numeric' => 'Tarif 1 jam pertama harus berupa angka.',
            'tarif_pertama.min' => 'Tarif 1 jam pertama tidak boleh negatif.',
            'tarif_maksimal.numeric' => 'Tarif maksimal harus berupa angka.',
            'tarif_maksimal.min' => 'Tarif maksimal tidak boleh negatif.',
        ];
    }
}
