<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaParkir extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::saved(function ($area) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('tb_area_parkir')) {
                    \Illuminate\Support\Facades\DB::table('tb_area_parkir')->updateOrInsert(
                        ['id_area' => $area->id],
                        [
                            'nama_area' => substr($area->nama_area, 0, 50),
                            'kapasitas' => $area->kapasitas_total ?? 20,
                            'terisi' => $area->slot_terisi ?? 0,
                            'created_at' => $area->created_at ?? now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            } catch (\Throwable $e) {}
        });

        static::deleted(function ($area) {
            try {
                \Illuminate\Support\Facades\DB::table('tb_area_parkir')->where('id_area', $area->id)->delete();
            } catch (\Throwable $e) {}
        });
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'area_parkir_id');
    }

    public function parkirAktif()
    {
        return $this->hasMany(Transaksi::class, 'area_parkir_id')->where('status', 'parkir');
    }

    public function getSlotTerisiAttribute(): int
    {
        return $this->parkirAktif()->count();
    }

    public function getSlotTersediaAttribute(): int
    {
        return max(0, $this->kapasitas_total - $this->slot_terisi);
    }

    public function getStatusKapasitasAttribute(): string
    {
        return $this->slot_terisi >= $this->kapasitas_total ? 'Penuh' : 'Tersedia';
    }

    public function getPersentaseTerisiAttribute(): float
    {
        if ($this->kapasitas_total <= 0) {
            return 0;
        }
        return round(($this->slot_terisi / $this->kapasitas_total) * 100, 1);
    }
}
