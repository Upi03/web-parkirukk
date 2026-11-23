<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AreaParkir;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');
        $today = now()->startOfDay();

        // 1. Hitung Statistik Real dari Database
        $totalSlot = (int) AreaParkir::where('status', 'aktif')->sum('kapasitas_total');
        if ($totalSlot === 0) {
            $totalSlot = (int) AreaParkir::sum('kapasitas_total');
        }

        $totalTerparkir = Transaksi::where('status', 'parkir')->count();
        $slotTersedia = max(0, $totalSlot - $totalTerparkir);

        $kendaraanHariIni = Transaksi::whereDate('waktu_masuk', $today)->count();
        $pendapatanHariIni = (float) Transaksi::whereDate('waktu_keluar', $today)
            ->where('status', 'selesai')
            ->sum('total_bayar');

        $totalPetugas = User::where('role', 'petugas')->where('is_active', true)->count();

        // Perbandingan kemarin untuk trend / info
        $yesterday = now()->subDay()->startOfDay();
        $kendaraanKemarin = Transaksi::whereDate('waktu_masuk', $yesterday)->count();
        $pendapatanKemarin = (float) Transaksi::whereDate('waktu_keluar', $yesterday)
            ->where('status', 'selesai')
            ->sum('total_bayar');

        $stats = [
            'total_slot' => [
                'value' => $totalSlot,
                'info' => "$slotTersedia slot tersedia",
                'trend' => $slotTersedia > 0 ? 'naik' : 'turun'
            ],
            'kendaraan_hari_ini' => [
                'value' => $kendaraanHariIni,
                'info' => "Hari ini (" . ($kendaraanHariIni >= $kendaraanKemarin ? '+' : '') . ($kendaraanHariIni - $kendaraanKemarin) . " vs kemarin)",
                'trend' => $kendaraanHariIni >= $kendaraanKemarin ? 'naik' : 'turun'
            ],
            'pendapatan_hari_ini' => [
                'value' => $pendapatanHariIni,
                'info' => "Hari ini (" . ($pendapatanHariIni >= $pendapatanKemarin ? '+' : '') . number_format($pendapatanHariIni - $pendapatanKemarin, 0, ',', '.') . ")",
                'trend' => $pendapatanHariIni >= $pendapatanKemarin ? 'naik' : 'turun'
            ],
            'total_petugas' => [
                'value' => $totalPetugas,
                'info' => "Petugas aktif",
                'trend' => 'netral'
            ]
        ];

        // 2. Grafik: Pendapatan 7 hari terakhir (kronologis dari 6 hari lalu sampai hari ini)
        $chartPendapatan = [
            'labels' => [],
            'data' => []
        ];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartPendapatan['labels'][] = $date->translatedFormat('d M');
            $chartPendapatan['data'][] = (float) Transaksi::whereDate('waktu_keluar', $date->toDateString())
                ->where('status', 'selesai')
                ->sum('total_bayar');
        }

        // 3. Grafik bar: Jumlah kendaraan masuk per jam hari ini (06:00 - 22:00)
        $chartMasukPerJam = [
            'labels' => [],
            'data' => []
        ];
        for ($i = 6; $i <= 22; $i++) {
            $startHour = sprintf('%02d:00:00', $i);
            $endHour = sprintf('%02d:59:59', $i);
            $chartMasukPerJam['labels'][] = sprintf('%02d:00', $i);
            $chartMasukPerJam['data'][] = Transaksi::whereDate('waktu_masuk', $today->toDateString())
                ->whereTime('waktu_masuk', '>=', $startHour)
                ->whereTime('waktu_masuk', '<=', $endHour)
                ->count();
        }

        // 4. Tabel Aktivitas Terbaru dari tabel ActivityLog (5 data terbaru)
        $recentActivities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 5. Ringkasan Area Parkir (dihitung dari database per area)
        $areas = AreaParkir::all();
        $areaSummary = [];
        foreach ($areas as $area) {
            $terisi = $area->slot_terisi;
            $kapasitas = max(1, $area->kapasitas_total);
            $status = ($terisi >= $kapasitas) ? 'Penuh' : 'Tersedia';

            $areaSummary[] = [
                'id' => $area->id,
                'nama' => $area->nama_area,
                'kapasitas' => $area->kapasitas_total,
                'terisi' => $terisi,
                'status' => $status,
                'persentase' => round(($terisi / $kapasitas) * 100),
            ];
        }

        // Area peringatan untuk notifikasi topbar
        $alertAreas = array_filter($areaSummary, function ($a) {
            return $a['persentase'] >= 90;
        });

        return view('Admin.Dashboard.index', compact(
            'stats',
            'chartPendapatan',
            'chartMasukPerJam',
            'recentActivities',
            'areaSummary',
            'alertAreas'
        ));
    }
}
