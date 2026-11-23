<?php

namespace App\Http\Requests\Admin;

use App\Models\AreaParkir;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAreaParkirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        $areaId = $this->route('area') instanceof AreaParkir 
            ? $this->route('area')->id 
            : $this->route('area');

        return [
            'nama_area' => [
                'required',
                'string',
                'max:100',
                Rule::unique('area_parkirs')->ignore($areaId),
            ],
            'kapasitas_total' => 'required|integer|min:1',
            'keterangan' => 'nullable|string|max:500',
            'status' => 'required|in:aktif,nonaktif',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $area = $this->route('area') instanceof AreaParkir 
                ? $this->route('area') 
                : AreaParkir::find($this->route('area'));

            if ($area) {
                $terisi = $area->slot_terisi;
                if ($this->kapasitas_total < $terisi) {
                    $validator->errors()->add(
                        'kapasitas_total',
                        "Kapasitas tidak boleh lebih kecil dari jumlah kendaraan yang sedang parkir ({$terisi} kendaraan)."
                    );
                }
            }
        });
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
