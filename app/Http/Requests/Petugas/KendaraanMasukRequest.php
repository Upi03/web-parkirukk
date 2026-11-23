<?php

namespace App\Http\Requests\Petugas;

use App\Models\AreaParkir;
use App\Models\Transaksi;
use Illuminate\Foundation\Http\FormRequest;

class KendaraanMasukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'petugas';
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('plat_nomor')) {
            // Ubah otomatis ke huruf kapital dan rapikan spasi berlebih
            $plat = preg_replace('/\s+/', ' ', trim($this->plat_nomor));
            $this->merge([
                'plat_nomor' => strtoupper($plat),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'plat_nomor' => [
                'required',
                'string',
                'min:3',
                'max:20',
                function ($attribute, $value, $fail) {
                    $isParkir = Transaksi::where('plat_nomor', $value)
                        ->where('status', 'parkir')
                        ->exists();
                    if ($isParkir) {
                        $fail("Kendaraan dengan plat nomor {$value} masih berstatus parkir aktif di sistem.");
                    }
                },
            ],
            'tarif_id' => ['required', 'exists:tarifs,id'],
            'area_parkir_id' => [
                'required',
                'exists:area_parkirs,id',
                function ($attribute, $value, $fail) {
                    $area = AreaParkir::find($value);
                    if (!$area || $area->status !== 'aktif') {
                        $fail("Area parkir yang dipilih sedang tidak aktif atau tidak ditemukan.");
                        return;
                    }
                    if ($area->slot_tersedia <= 0) {
                        $fail("Area parkir {$area->nama_area} sudah penuh! Silakan pilih area parkir lain.");
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'plat_nomor.required' => 'Plat nomor kendaraan wajib diisi.',
            'plat_nomor.min' => 'Plat nomor minimal 3 karakter.',
            'plat_nomor.max' => 'Plat nomor maksimal 20 karakter.',
            'tarif_id.required' => 'Jenis kendaraan wajib dipilih.',
            'tarif_id.exists' => 'Jenis kendaraan yang dipilih tidak valid.',
            'area_parkir_id.required' => 'Area parkir wajib dipilih.',
            'area_parkir_id.exists' => 'Area parkir yang dipilih tidak valid.',
        ];
    }
}
