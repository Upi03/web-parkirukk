<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::saved(function ($tarif) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('tb_tarif')) {
                    $jenisLower = strtolower($tarif->jenis_kendaraan);
                    $jenis = str_contains($jenisLower, 'motor') ? 'motor' : (str_contains($jenisLower, 'mobil') ? 'mobil' : 'lainnya');
                    \Illuminate\Support\Facades\DB::table('tb_tarif')->updateOrInsert(
                        ['id_tarif' => $tarif->id],
                        [
                            'jenis_kendaraan' => $jenis,
                            'tarif_per_jam' => round($tarif->tarif_per_jam),
                            'created_at' => $tarif->created_at ?? now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            } catch (\Throwable $e) {}
        });

        static::deleted(function ($tarif) {
            try {
                \Illuminate\Support\Facades\DB::table('tb_tarif')->where('id_tarif', $tarif->id)->delete();
            } catch (\Throwable $e) {}
        });
    }

    protected function casts(): array
    {
        return [
            'tarif_per_jam' => 'decimal:2',
            'tarif_pertama' => 'decimal:2',
            'tarif_maksimal' => 'decimal:2',
        ];
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class);
    }
}
