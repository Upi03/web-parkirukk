<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\AreaParkir;
use App\Models\Tarif;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');
        $today = now()->startOfDay();
        $user = auth()->user();

        // 1. Hitung Statistik Riil dari Database
        $kendaraanMasukHariIni = Transaksi::whereDate('waktu_masuk', $today)->count();
        $sedangParkir = Transaksi::where('status', 'parkir')->count();
        $kendaraanKeluarHariIni = Transaksi::whereDate('waktu_keluar', $today)
            ->where('status', 'selesai')
            ->count();

        // Pendapatan hari ini khusus yang ditangani petugas yang sedang login pada shift hari ini
        $pendapatanShift = (float) Transaksi::whereDate('waktu_keluar', $today)
            ->where('petugas_id', $user->id)
            ->where('status', 'selesai')
            ->sum('total_bayar');

        // Kemarin untuk perbandingan tren
        $yesterday = now()->subDay()->startOfDay();
        $masukKemarin = Transaksi::whereDate('waktu_masuk', $yesterday)->count();
        $keluarKemarin = Transaksi::whereDate('waktu_keluar', $yesterday)->where('status', 'selesai')->count();

        $stats = [
            'kendaraan_masuk' => [
                'value' => $kendaraanMasukHariIni,
                'trend' => $kendaraanMasukHariIni >= $masukKemarin ? 'naik' : 'turun',
                'percentage' => 'Hari ini'
            ],
            'sedang_parkir' => [
                'value' => $sedangParkir,
                'trend' => 'netral',
                'percentage' => 'Saat ini'
            ],
            'kendaraan_keluar' => [
                'value' => $kendaraanKeluarHariIni,
                'trend' => $kendaraanKeluarHariIni >= $keluarKemarin ? 'naik' : 'turun',
                'percentage' => 'Hari ini'
            ],
            'pendapatan' => [
                'value' => $pendapatanShift,
                'trend' => 'naik',
                'percentage' => 'Shift saya'
            ]
        ];

        // 2. Ketersediaan Kapasitas Per Jenis Kendaraan & Per Area (Sinkron dengan Database & Admin)
        $activeAreas = AreaParkir::where('status', 'aktif')->get();
        $tarifs = Tarif::all();

        $slotAvailability = [];
        foreach ($tarifs as $tarif) {
            $jenis = $tarif->jenis_kendaraan;
            $jenisLower = strtolower($jenis);

            // Hitung kapasitas total untuk jenis ini dari area aktif
            $capacity = $activeAreas->filter(function ($area) use ($jenis, $jenisLower) {
                if (!empty($area->jenis_kendaraan)) {
                    return strcasecmp($area->jenis_kendaraan, $jenis) === 0;
                }
                // Jika jenis_kendaraan di area belum diset, cocokkan kata kunci nama_area
                return str_contains(strtolower($area->nama_area), $jenisLower);
            })->sum('kapasitas_total');

            // Jika tidak ada area spesifik untuk jenis ini, alokasikan dari kapasitas total umum
            if ($capacity <= 0) {
                $capacity = max(10, (int) round($activeAreas->sum('kapasitas_total') / max(1, $tarifs->count())));
            }

            $terisi = Transaksi::where('status', 'parkir')
                ->where('jenis_kendaraan', $jenis)
                ->count();

            $kosong = max(0, $capacity - $terisi);
            $persentase = round(($terisi / max(1, $capacity)) * 100, 1);

            $slotAvailability[$jenisLower] = [
                'jenis' => $jenis,
                'kapasitas' => $capacity,
                'terisi' => $terisi,
                'kosong' => $kosong,
                'persentase' => min(100, $persentase),
            ];
        }

        // Data area parkir untuk ringkasan cepat petugas
        $areaSummaries = $activeAreas->map(function ($area) {
            return [
                'id' => $area->id,
                'nama' => $area->nama_area,
                'kapasitas' => $area->kapasitas_total,
                'terisi' => $area->slot_terisi,
                'tersedia' => $area->slot_tersedia,
                'persentase' => $area->persentase_terisi,
                'status' => $area->status_kapasitas,
            ];
        });

        // 3. Tabel Transaksi Terakhir (Selesai) - 5 transaksi selesai terbaru
        $recentTransactions = Transaksi::with(['tarif', 'petugas'])
            ->where('status', 'selesai')
            ->orderBy('waktu_keluar', 'desc')
            ->take(5)
            ->get();

        // 4. Tanggal Server Bahasa Indonesia
        $serverDate = now()->translatedFormat('d M Y');

        return view('Petugas.Dashboard.index', compact(
            'stats',
            'slotAvailability',
            'areaSummaries',
            'recentTransactions',
            'serverDate'
        ));
    }
}
