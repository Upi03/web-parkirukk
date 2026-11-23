<?php

namespace App\Http\Requests\Admin;

use App\Models\AreaParkir;
use App\Models\Transaksi;
use Illuminate\Foundation\Http\FormRequest;

class StoreKendaraanRequest extends FormRequest
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
            'area_parkir_id' => 'required|exists:area_parkirs,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $plat = strtoupper(trim($this->plat_nomor ?? ''));
            if ($plat) {
                $isParkir = Transaksi::where('plat_nomor', $plat)
                    ->where('status', 'parkir')
                    ->exists();

                if ($isParkir) {
                    $validator->errors()->add('plat_nomor', "Kendaraan dengan plat {$plat} saat ini masih berstatus parkir.");
                }
            }

            if ($this->area_parkir_id) {
                $area = AreaParkir::find($this->area_parkir_id);
                if ($area && $area->slot_tersedia <= 0) {
                    $validator->errors()->add('area_parkir_id', "Area parkir {$area->nama_area} sudah penuh.");
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'plat_nomor.required' => 'Plat nomor kendaraan wajib diisi.',
            'tarif_id.required' => 'Jenis kendaraan / tarif wajib dipilih.',
            'tarif_id.exists' => 'Tarif yang dipilih tidak valid.',
            'area_parkir_id.required' => 'Area parkir wajib dipilih.',
            'area_parkir_id.exists' => 'Area parkir yang dipilih tidak valid.',
        ];
    }
}
