<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
        'total_bayar' => 'decimal:2',
        'uang_diterima' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'tarif_snapshot' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function ($tx) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('tb_transaksi') && \Illuminate\Support\Facades\Schema::hasTable('tb_kendaraan')) {
                    $plat = substr(strtoupper(trim($tx->plat_nomor ?? 'B 1234 XX')), 0, 15);
                    $jenis = substr($tx->jenis_kendaraan ?? 'mobil', 0, 20);
                    $userId = $tx->petugas_id ?? 1;

                    $kendaraan = \Illuminate\Support\Facades\DB::table('tb_kendaraan')->where('plat_nomor', $plat)->first();
                    if (!$kendaraan) {
                        $idKendaraan = \Illuminate\Support\Facades\DB::table('tb_kendaraan')->insertGetId([
                            'plat_nomor' => $plat,
                            'jenis_kendaraan' => $jenis,
                            'id_user' => $userId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $idKendaraan = $kendaraan->id_kendaraan;
                    }

                    $status = ($tx->status === 'parkir') ? 'masuk' : 'keluar';
                    $durasiJam = $tx->durasi_menit ? ceil($tx->durasi_menit / 60) : null;
                    $biayaTotal = $tx->total_bayar ? round($tx->total_bayar) : null;

                    \Illuminate\Support\Facades\DB::table('tb_transaksi')->updateOrInsert(
                        ['id_parkir' => $tx->id],
                        [
                            'id_kendaraan' => $idKendaraan,
                            'waktu_masuk' => $tx->waktu_masuk,
                            'waktu_keluar' => $tx->waktu_keluar,
                            'id_tarif' => $tx->tarif_id ?? 1,
                            'durasi_jam' => $durasiJam,
                            'biaya_total' => $biayaTotal,
                            'status' => $status,
                            'id_user' => $userId,
                            'id_area' => $tx->area_parkir_id ?? 1,
                            'created_at' => $tx->created_at ?? now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            } catch (\Throwable $e) {}
        });

        static::deleted(function ($tx) {
            try {
                \Illuminate\Support\Facades\DB::table('tb_transaksi')->where('id_parkir', $tx->id)->delete();
            } catch (\Throwable $e) {}
        });
    }

    public function tarif()
    {
        return $this->belongsTo(Tarif::class);
    }

    public function areaParkir()
    {
        return $this->belongsTo(AreaParkir::class, 'area_parkir_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    /**
     * Hitung durasi dan estimasi biaya berdasarkan tarif (atau snapshot tarif jika sudah disimpan)
     */
    public function hitungBiaya($waktuKeluar = null): array
    {
        $waktuKeluar = $waktuKeluar ? Carbon::parse($waktuKeluar) : now();
        $waktuMasuk = Carbon::parse($this->waktu_masuk);
        $durasiMenit = max(1, $waktuMasuk->diffInMinutes($waktuKeluar));

        // Cek toleransi gratis jika diatur di pengaturan sistem
        $toleransi = (int) Setting::get('tolerance_minutes', 0);
        if ($durasiMenit <= $toleransi) {
            return [
                'durasi_menit' => $durasiMenit,
                'durasi_jam' => 0,
                'total_bayar' => 0,
            ];
        }

        $durasiJam = (int) ceil($durasiMenit / 60);

        // Gunakan snapshot jika ada (agar perubahan tarif oleh admin tidak mengubah transaksi lama)
        $tarifSnapshot = $this->tarif_snapshot;
        if ($tarifSnapshot) {
            $tarifPertama = isset($tarifSnapshot['tarif_pertama']) && $tarifSnapshot['tarif_pertama'] !== null
                ? (float) $tarifSnapshot['tarif_pertama']
                : (float) ($tarifSnapshot['tarif_per_jam'] ?? 0);
            $tarifPerJam = (float) ($tarifSnapshot['tarif_per_jam'] ?? 0);
            $tarifMaksimal = isset($tarifSnapshot['tarif_maksimal']) && $tarifSnapshot['tarif_maksimal'] !== null
                ? (float) $tarifSnapshot['tarif_maksimal']
                : null;
        } else {
            $tarif = $this->tarif;
            if (!$tarif) {
                return [
                    'durasi_menit' => $durasiMenit,
                    'durasi_jam' => $durasiJam,
                    'total_bayar' => 0,
                ];
            }

            $tarifPertama = $tarif->tarif_pertama !== null ? (float) $tarif->tarif_pertama : (float) $tarif->tarif_per_jam;
            $tarifPerJam = (float) $tarif->tarif_per_jam;
            $tarifMaksimal = $tarif->tarif_maksimal !== null ? (float) $tarif->tarif_maksimal : null;
        }

        if ($durasiJam <= 1) {
            $totalBayar = $tarifPertama;
        } else {
            $totalBayar = $tarifPertama + (($durasiJam - 1) * $tarifPerJam);
        }

        // Cek tarif maksimal harian jika diatur
        if ($tarifMaksimal && $tarifMaksimal > 0) {
            $hari = ceil($durasiJam / 24);
            $maxAllowed = $hari * $tarifMaksimal;
            if ($totalBayar > $maxAllowed) {
                $totalBayar = $maxAllowed;
            }
        }

        return [
            'durasi_menit' => $durasiMenit,
            'durasi_jam' => $durasiJam,
            'total_bayar' => $totalBayar,
        ];
    }
}
