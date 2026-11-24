<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AreaParkir;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OwnerController extends Controller
{
    /**
     * Tampilkan halaman Dashboard Owner dengan statistik, grafik, dan rekap transaksi.
     */
    public function dashboard(Request $request)
    {
        Carbon::setLocale('id');

        $rangeData = $this->parseDateRange($request);
        $startDate = $rangeData['start_date'];
        $endDate = $rangeData['end_date'];
        $rangeKey = $rangeData['range_key'];
        $prevStartDate = $rangeData['prev_start_date'];
        $prevEndDate = $rangeData['prev_end_date'];

        // 1. Hitung Statistik Periode Terpilih & Periode Sebelumnya
        $stats = $this->calculateStats($startDate, $endDate, $prevStartDate, $prevEndDate);

        // 2. Data Grafik Pendapatan
        $chartPendapatan = $this->getRevenueChartData($startDate, $endDate);

        // 3. Data Grafik Volume Kendaraan
        $chartVolume = $this->getVehicleVolumeChartData($startDate, $endDate);

        // 4. Distribusi Metode Pembayaran
        $paymentMethods = $this->getPaymentDistribution($startDate, $endDate);

        // 5. Query Tabel Transaksi (dengan search & filter)
        $transaksiQuery = Transaksi::with(['petugas', 'tarif', 'areaParkir'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $transaksiQuery->where(function ($query) use ($q) {
                $query->where('plat_nomor', 'like', "%{$q}%")
                    ->orWhere('kode_tiket', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $transaksiQuery->where('status', $request->input('status'));
        }

        if ($request->filled('metode_bayar') && $request->input('metode_bayar') !== 'all') {
            $transaksiQuery->where('metode_bayar', $request->input('metode_bayar'));
        }

        if ($request->filled('jenis_kendaraan') && $request->input('jenis_kendaraan') !== 'all') {
            $transaksiQuery->where('jenis_kendaraan', $request->input('jenis_kendaraan'));
        }

        $transaksis = $transaksiQuery->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('Owner.Dashboard.index', compact(
            'stats',
            'chartPendapatan',
            'chartVolume',
            'paymentMethods',
            'transaksis',
            'startDate',
            'endDate',
            'rangeKey'
        ));
    }

    /**
     * Endpoint API JSON untuk pembaruan filter tanggal secara dinamis (AJAX) tanpa reload penuh.
     */
    public function data(Request $request)
    {
        Carbon::setLocale('id');

        $rangeData = $this->parseDateRange($request);
        $startDate = $rangeData['start_date'];
        $endDate = $rangeData['end_date'];
        $prevStartDate = $rangeData['prev_start_date'];
        $prevEndDate = $rangeData['prev_end_date'];

        $stats = $this->calculateStats($startDate, $endDate, $prevStartDate, $prevEndDate);
        $chartPendapatan = $this->getRevenueChartData($startDate, $endDate);
        $chartVolume = $this->getVehicleVolumeChartData($startDate, $endDate);
        $paymentMethods = $this->getPaymentDistribution($startDate, $endDate);

        return response()->json([
            'success' => true,
            'range_key' => $rangeData['range_key'],
            'start_date_formatted' => $startDate->translatedFormat('d M Y'),
            'end_date_formatted' => $endDate->translatedFormat('d M Y'),
            'stats' => $stats,
            'chart_pendapatan' => $chartPendapatan,
            'chart_volume' => $chartVolume,
            'payment_methods' => $paymentMethods,
        ]);
    }

    /**
     * Export rekap data transaksi parkir ke file CSV dengan format rapi dan BOM UTF-8.
     */
    public function export(Request $request): StreamedResponse
    {
        Carbon::setLocale('id');

        $rangeData = $this->parseDateRange($request);
        $startDate = $rangeData['start_date'];
        $endDate = $rangeData['end_date'];

        $filename = 'rekap-transaksi-smarpark_' . $startDate->format('Ymd') . '_sd_' . $endDate->format('Ymd') . '.csv';

        $query = Transaksi::with(['petugas', 'tarif', 'areaParkir'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('plat_nomor', 'like', "%{$q}%")
                    ->orWhere('kode_tiket', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('metode_bayar') && $request->input('metode_bayar') !== 'all') {
            $query->where('metode_bayar', $request->input('metode_bayar'));
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($query, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM untuk kompatibilitas MS Excel
            fputs($handle, "\xEF\xBB\xBF");

            // Header informasi rekapitulasi
            fputcsv($handle, ['REKAPITULASI TRANSAKSI PARKIR - SmarPARK']);
            fputcsv($handle, ['Periode:', $startDate->translatedFormat('d F Y') . ' s/d ' . $endDate->translatedFormat('d F Y')]);
            fputcsv($handle, ['Tanggal Unduh:', now()->translatedFormat('d F Y H:i:s')]);
            fputcsv($handle, []); // Baris kosong

            // Kolom tabel
            fputcsv($handle, [
                'No',
                'Kode Tiket',
                'Plat Nomor',
                'Jenis Kendaraan',
                'Area Parkir',
                'Waktu Masuk',
                'Waktu Keluar',
                'Durasi (Menit)',
                'Durasi Format',
                'Total Tarif (Rp)',
                'Metode Pembayaran',
                'Status',
                'Petugas Pencatat',
            ]);

            $no = 1;
            $totalPendapatan = 0;
            $totalSelesai = 0;

            $query->orderBy('created_at', 'asc')->chunk(200, function ($transaksis) use ($handle, &$no, &$totalPendapatan, &$totalSelesai) {
                foreach ($transaksis as $t) {
                    $durasiFormat = '-';
                    if ($t->durasi_menit !== null && $t->durasi_menit > 0) {
                        $jam = floor($t->durasi_menit / 60);
                        $menit = $t->durasi_menit % 60;
                        $durasiFormat = ($jam > 0 ? "{$jam} jam " : '') . "{$menit} mnt";
                    }

                    if ($t->status === 'selesai' && $t->total_bayar) {
                        $totalPendapatan += (float) $t->total_bayar;
                        $totalSelesai++;
                    }

                    fputcsv($handle, [
                        $no++,
                        $t->kode_tiket,
                        $t->plat_nomor,
                        $t->jenis_kendaraan,
                        $t->areaParkir ? $t->areaParkir->nama_area : '-',
                        $t->waktu_masuk ? $t->waktu_masuk->translatedFormat('d/m/Y H:i') : '-',
                        $t->waktu_keluar ? $t->waktu_keluar->translatedFormat('d/m/Y H:i') : '-',
                        $t->durasi_menit ?? 0,
                        $durasiFormat,
                        $t->total_bayar ? number_format((float) $t->total_bayar, 0, ',', '.') : '0',
                        $t->metode_bayar ?? '-',
                        ucfirst($t->status),
                        $t->petugas ? $t->petugas->name : '-',
                    ]);
                }
            });

            // Ringkasan di footer CSV
            fputcsv($handle, []);
            fputcsv($handle, ['', '', '', '', '', '', '', '', 'Total Pendapatan:', 'Rp ' . number_format($totalPendapatan, 0, ',', '.')]);
            fputcsv($handle, ['', '', '', '', '', '', '', '', 'Total Transaksi Selesai:', $totalSelesai]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Hitung metrik ringkasan dan persentase perubahan vs periode sebelumnya.
     */
    private function calculateStats($startDate, $endDate, $prevStartDate, $prevEndDate): array
    {
        // 1. Total Pendapatan (periode aktif vs periode sebelumnya)
        $pendapatan = (float) Transaksi::whereBetween('waktu_keluar', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->sum('total_bayar');

        $prevPendapatan = (float) Transaksi::whereBetween('waktu_keluar', [$prevStartDate, $prevEndDate])
            ->where('status', 'selesai')
            ->sum('total_bayar');

        // 2. Total Transaksi
        $totalTransaksi = Transaksi::whereBetween('created_at', [$startDate, $endDate])->count();
        $prevTotalTransaksi = Transaksi::whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();

        // 3. Kendaraan Masuk Hari Ini (dan perbandingan vs kemarin)
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $yesterdayStart = now()->subDay()->startOfDay();
        $yesterdayEnd = now()->subDay()->endOfDay();

        $kendaraanHariIni = Transaksi::whereBetween('waktu_masuk', [$todayStart, $todayEnd])->count();
        $kendaraanKemarin = Transaksi::whereBetween('waktu_masuk', [$yesterdayStart, $yesterdayEnd])->count();

        // 4. Rata-rata Durasi Parkir (dalam menit)
        $avgDurasi = (float) Transaksi::whereBetween('waktu_keluar', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->whereNotNull('durasi_menit')
            ->avg('durasi_menit');

        $prevAvgDurasi = (float) Transaksi::whereBetween('waktu_keluar', [$prevStartDate, $prevEndDate])
            ->where('status', 'selesai')
            ->whereNotNull('durasi_menit')
            ->avg('durasi_menit');

        return [
            'pendapatan' => [
                'raw_value' => $pendapatan,
                'formatted' => 'Rp ' . number_format($pendapatan, 0, ',', '.'),
                'change_pct' => $this->calculatePercentageChange($pendapatan, $prevPendapatan),
                'trend' => $pendapatan >= $prevPendapatan ? 'up' : 'down',
                'prev_formatted' => 'Rp ' . number_format($prevPendapatan, 0, ',', '.'),
            ],
            'transaksi' => [
                'raw_value' => $totalTransaksi,
                'formatted' => number_format($totalTransaksi, 0, ',', '.'),
                'change_pct' => $this->calculatePercentageChange($totalTransaksi, $prevTotalTransaksi),
                'trend' => $totalTransaksi >= $prevTotalTransaksi ? 'up' : 'down',
                'prev_formatted' => number_format($prevTotalTransaksi, 0, ',', '.'),
            ],
            'kendaraan_hari_ini' => [
                'raw_value' => $kendaraanHariIni,
                'formatted' => number_format($kendaraanHariIni, 0, ',', '.'),
                'change_pct' => $this->calculatePercentageChange($kendaraanHariIni, $kendaraanKemarin),
                'trend' => $kendaraanHariIni >= $kendaraanKemarin ? 'up' : 'down',
                'prev_formatted' => number_format($kendaraanKemarin, 0, ',', '.') . ' kemarin',
            ],
            'avg_durasi' => [
                'raw_minutes' => round($avgDurasi),
                'formatted' => $this->formatDuration((int) round($avgDurasi)),
                'change_pct' => $this->calculatePercentageChange($avgDurasi, $prevAvgDurasi),
                'trend' => $avgDurasi >= $prevAvgDurasi ? 'up' : 'down',
                'prev_formatted' => $this->formatDuration((int) round($prevAvgDurasi)),
            ],
        ];
    }

    /**
     * Hitung data chart pendapatan (Line Chart) berdasarkan rentang tanggal.
     */
    private function getRevenueChartData($startDate, $endDate): array
    {
        $daysDiff = $startDate->diffInDays($endDate);

        $labels = [];
        $data = [];

        if ($daysDiff <= 1) {
            // Rentang 1 hari: Tampilkan per 2 jam (06:00, 08:00, ..., 22:00)
            for ($h = 6; $h <= 22; $h += 2) {
                $startHour = sprintf('%02d:00:00', $h);
                $endHour = sprintf('%02d:59:59', $h + 1);
                $labels[] = sprintf('%02d:00', $h);

                $rev = (float) Transaksi::whereDate('waktu_keluar', $startDate->toDateString())
                    ->whereTime('waktu_keluar', '>=', $startHour)
                    ->whereTime('waktu_keluar', '<=', $endHour)
                    ->where('status', 'selesai')
                    ->sum('total_bayar');

                $data[] = $rev;
            }
        } else {
            // Rentang hari: Loop setiap hari dari start sampai end
            $cursor = $startDate->copy()->startOfDay();
            $limitDays = min($daysDiff + 1, 31); // Max 31 point agar chart tetap bersih

            $step = ceil($daysDiff / 14); // Jika lebih dari 14 hari, langkah per step hari
            if ($step < 1)
                $step = 1;

            while ($cursor->lte($endDate)) {
                $currentDate = $cursor->copy();
                $labels[] = $currentDate->translatedFormat('d M');

                $rev = (float) Transaksi::whereDate('waktu_keluar', $currentDate->toDateString())
                    ->where('status', 'selesai')
                    ->sum('total_bayar');

                $data[] = $rev;
                $cursor = $cursor->addDays($step);
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total_formatted' => 'Rp ' . number_format(array_sum($data), 0, ',', '.'),
        ];
    }

    /**
     * Hitung data chart volume kendaraan masuk (Bar Chart) beserta breakdown jenisnya.
     */
    private function getVehicleVolumeChartData($startDate, $endDate): array
    {
        $daysDiff = $startDate->diffInDays($endDate);

        $labels = [];
        $totalVehicles = [];
        $mobil = [];
        $motor = [];
        $lainnya = [];

        if ($daysDiff <= 1) {
            for ($h = 6; $h <= 22; $h += 2) {
                $startHour = sprintf('%02d:00:00', $h);
                $endHour = sprintf('%02d:59:59', $h + 1);
                $labels[] = sprintf('%02d:00', $h);

                $baseQuery = Transaksi::whereDate('waktu_masuk', $startDate->toDateString())
                    ->whereTime('waktu_masuk', '>=', $startHour)
                    ->whereTime('waktu_masuk', '<=', $endHour);

                $total = (clone $baseQuery)->count();
                $mob = (clone $baseQuery)->where('jenis_kendaraan', 'Mobil')->count();
                $mot = (clone $baseQuery)->where('jenis_kendaraan', 'Motor')->count();
                $oth = $total - ($mob + $mot);

                $totalVehicles[] = $total;
                $mobil[] = $mob;
                $motor[] = $mot;
                $lainnya[] = max(0, $oth);
            }
        } else {
            $cursor = $startDate->copy()->startOfDay();
            $step = ceil($daysDiff / 14);
            if ($step < 1)
                $step = 1;

            while ($cursor->lte($endDate)) {
                $currentDate = $cursor->copy();
                $labels[] = $currentDate->translatedFormat('d M');

                $baseQuery = Transaksi::whereDate('waktu_masuk', $currentDate->toDateString());

                $total = (clone $baseQuery)->count();
                $mob = (clone $baseQuery)->where('jenis_kendaraan', 'Mobil')->count();
                $mot = (clone $baseQuery)->where('jenis_kendaraan', 'Motor')->count();
                $oth = $total - ($mob + $mot);

                $totalVehicles[] = $total;
                $mobil[] = $mob;
                $motor[] = $mot;
                $lainnya[] = max(0, $oth);

                $cursor = $cursor->addDays($step);
            }
        }

        return [
            'labels' => $labels,
            'total' => $totalVehicles,
            'mobil' => $mobil,
            'motor' => $motor,
            'lainnya' => $lainnya,
        ];
    }

    /**
     * Hitung distribusi persentase dan total pembayaran (Tunai vs QRIS vs Transfer).
     */
    private function getPaymentDistribution($startDate, $endDate): array
    {
        $transactions = Transaksi::whereBetween('waktu_keluar', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->selectRaw('COALESCE(metode_bayar, "Tunai") as metode, COUNT(*) as count, SUM(total_bayar) as total')
            ->groupBy('metode')
            ->get();

        $totalCount = $transactions->sum('count');
        $totalNominal = $transactions->sum('total');

        $result = [
            'tunai' => ['count' => 0, 'nominal' => 0, 'pct' => 0],
            'qris' => ['count' => 0, 'nominal' => 0, 'pct' => 0],
            'transfer' => ['count' => 0, 'nominal' => 0, 'pct' => 0],
            'total_count' => $totalCount,
            'total_nominal' => $totalNominal,
            'total_nominal_formatted' => 'Rp ' . number_format($totalNominal, 0, ',', '.'),
        ];

        foreach ($transactions as $t) {
            $key = strtolower(trim($t->metode));
            if (str_contains($key, 'tunai') || str_contains($key, 'cash')) {
                $result['tunai']['count'] += $t->count;
                $result['tunai']['nominal'] += (float) $t->total;
            } elseif (str_contains($key, 'qris')) {
                $result['qris']['count'] += $t->count;
                $result['qris']['nominal'] += (float) $t->total;
            } else {
                $result['transfer']['count'] += $t->count;
                $result['transfer']['nominal'] += (float) $t->total;
            }
        }

        if ($totalCount > 0) {
            $result['tunai']['pct'] = round(($result['tunai']['count'] / $totalCount) * 100, 1);
            $result['qris']['pct'] = round(($result['qris']['count'] / $totalCount) * 100, 1);
            $result['transfer']['pct'] = round(($result['transfer']['count'] / $totalCount) * 100, 1);
        }

        $result['tunai']['nominal_formatted'] = 'Rp ' . number_format($result['tunai']['nominal'], 0, ',', '.');
        $result['qris']['nominal_formatted'] = 'Rp ' . number_format($result['qris']['nominal'], 0, ',', '.');
        $result['transfer']['nominal_formatted'] = 'Rp ' . number_format($result['transfer']['nominal'], 0, ',', '.');

        return $result;
    }

    /**
     * Parse query parameter rentang tanggal ke objek Carbon (start, end, prev_start, prev_end).
     */
    private function parseDateRange(Request $request): array
    {
        $rangeKey = $request->input('range', '7_days');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            try {
                $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                $rangeKey = 'custom';
            } catch (\Throwable $e) {
                $startDate = now()->subDays(6)->startOfDay();
                $endDate = now()->endOfDay();
                $rangeKey = '7_days';
            }
        } else {
            switch ($rangeKey) {
                case 'today':
                    $startDate = now()->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case 'this_month':
                    $startDate = now()->startOfMonth()->startOfDay();
                    $endDate = now()->endOfMonth()->endOfDay();
                    break;
                case '30_days':
                    $startDate = now()->subDays(29)->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case '7_days':
                default:
                    $startDate = now()->subDays(6)->startOfDay();
                    $endDate = now()->endOfDay();
                    $rangeKey = '7_days';
                    break;
            }
        }

        // Hitung periode perbandingan sebelumnya dengan durasi yang setara
        $daysDiff = max(1, $startDate->diffInDays($endDate) + 1);
        $prevEndDate = $startDate->copy()->subSecond();
        $prevStartDate = $prevEndDate->copy()->subDays($daysDiff - 1)->startOfDay();

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'prev_start_date' => $prevStartDate,
            'prev_end_date' => $prevEndDate,
            'range_key' => $rangeKey,
        ];
    }

    /**
     * Hitung persentase kenaikan/penurunan.
     */
    private function calculatePercentageChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        $change = (($current - $previous) / $previous) * 100;
        return round($change, 1);
    }

    /**
     * Halaman Rekap Transaksi lengkap untuk Owner dengan filter mendalam dan modal detail.
     */
    public function transaksi(Request $request)
    {
        Carbon::setLocale('id');

        $query = Transaksi::with(['petugas', 'tarif', 'areaParkir']);

        // Filter Rentang Tanggal
        $rangeKey = $request->input('range', '30_days');
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($rangeKey !== 'all') {
            switch ($rangeKey) {
                case 'today':
                    $startDate = now()->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case '7_days':
                    $startDate = now()->subDays(6)->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
                case 'this_month':
                    $startDate = now()->startOfMonth()->startOfDay();
                    $endDate = now()->endOfMonth()->endOfDay();
                    break;
                case '30_days':
                default:
                    $startDate = now()->subDays(29)->startOfDay();
                    $endDate = now()->endOfDay();
                    break;
            }
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $startDate = null;
            $endDate = null;
        }

        // Filter Pencarian
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('plat_nomor', 'like', "%{$q}%")
                    ->orWhere('kode_tiket', 'like', "%{$q}%");
            });
        }

        // Filter Jenis Kendaraan
        if ($request->filled('jenis_kendaraan') && $request->input('jenis_kendaraan') !== 'all') {
            $query->where('jenis_kendaraan', $request->input('jenis_kendaraan'));
        }

        // Filter Area Parkir
        if ($request->filled('area_id') && $request->input('area_id') !== 'all') {
            $query->where('area_parkir_id', $request->input('area_id'));
        }

        // Filter Petugas
        if ($request->filled('petugas_id') && $request->input('petugas_id') !== 'all') {
            $query->where('petugas_id', $request->input('petugas_id'));
        }

        // Filter Status
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Filter Metode Bayar
        if ($request->filled('metode_bayar') && $request->input('metode_bayar') !== 'all') {
            $query->where('metode_bayar', $request->input('metode_bayar'));
        }

        // KPI Ringkasan Filter
        $totalCount = (clone $query)->count();
        $totalPendapatan = (clone $query)->where('status', 'selesai')->sum('total_bayar');
        $totalParkirAktif = (clone $query)->where('status', 'parkir')->count();
        $totalQris = (clone $query)->where('status', 'selesai')->where('metode_bayar', 'QRIS')->count();

        $transaksis = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $areas = AreaParkir::orderBy('nama_area')->get();
        $petugasList = User::where('role', 'petugas')->orderBy('name')->get();

        return view('Owner.Transaksi.index', compact(
            'transaksis',
            'areas',
            'petugasList',
            'totalCount',
            'totalPendapatan',
            'totalParkirAktif',
            'totalQris',
            'rangeKey',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Detail transaksi untuk modal / audit inspeksi.
     */
    public function transaksiDetail(Transaksi $transaksi)
    {
        $transaksi->load(['petugas', 'tarif', 'areaParkir']);

        return response()->json([
            'id' => $transaksi->id,
            'kode_tiket' => $transaksi->kode_tiket,
            'plat_nomor' => $transaksi->plat_nomor,
            'jenis_kendaraan' => $transaksi->jenis_kendaraan,
            'area_parkir' => $transaksi->areaParkir ? $transaksi->areaParkir->nama_area : '-',
            'waktu_masuk' => $transaksi->waktu_masuk ? $transaksi->waktu_masuk->translatedFormat('d F Y, H:i') : '-',
            'waktu_keluar' => $transaksi->waktu_keluar ? $transaksi->waktu_keluar->translatedFormat('d F Y, H:i') : '-',
            'durasi_menit' => $transaksi->durasi_menit,
            'durasi_format' => $this->formatDuration((int) ($transaksi->durasi_menit ?? 0)),
            'total_bayar' => $transaksi->total_bayar,
            'total_bayar_formatted' => $transaksi->total_bayar ? 'Rp ' . number_format((float) $transaksi->total_bayar, 0, ',', '.') : '-',
            'uang_diterima' => $transaksi->uang_diterima ? 'Rp ' . number_format((float) $transaksi->uang_diterima, 0, ',', '.') : '-',
            'kembalian' => $transaksi->kembalian ? 'Rp ' . number_format((float) $transaksi->kembalian, 0, ',', '.') : '-',
            'metode_bayar' => $transaksi->metode_bayar ?? '-',
            'status' => $transaksi->status,
            'alasan_batal' => $transaksi->alasan_batal,
            'petugas' => $transaksi->petugas ? $transaksi->petugas->name : '-',
            'tarif_per_jam' => $transaksi->tarif ? 'Rp ' . number_format((float) $transaksi->tarif->tarif_per_jam, 0, ',', '.') : '-',
        ]);
    }

    /**
     * Halaman Laporan Eksekutif Keuangan & Operasional Parkir.
     */
    public function laporan(Request $request)
    {
        Carbon::setLocale('id');

        // Parameter bulan & tahun (default ke bulan & tahun ini)
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        // 1. KPI Ringkasan Periode
        $totalPendapatan = (float) Transaksi::whereBetween('waktu_keluar', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->sum('total_bayar');

        $totalTransaksi = Transaksi::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalSelesai = Transaksi::whereBetween('waktu_keluar', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->count();

        $avgDurasi = (float) Transaksi::whereBetween('waktu_keluar', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->whereNotNull('durasi_menit')
            ->avg('durasi_menit');

        $avgPerTransaksi = $totalSelesai > 0 ? ($totalPendapatan / $totalSelesai) : 0;

        // 2. Performa Berdasarkan Jenis Kendaraan
        $kendaraanReport = Transaksi::whereBetween('waktu_keluar', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->selectRaw('jenis_kendaraan, COUNT(*) as total_unit, SUM(total_bayar) as total_omset')
            ->groupBy('jenis_kendaraan')
            ->get()
            ->map(function ($item) use ($totalPendapatan) {
                $item->persentase = $totalPendapatan > 0 ? round(($item->total_omset / $totalPendapatan) * 100, 1) : 0;
                return $item;
            });

        // 3. Performa Berdasarkan Area Parkir
        $areaReport = Transaksi::whereBetween('waktu_keluar', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->whereNotNull('area_parkir_id')
            ->selectRaw('area_parkir_id, COUNT(*) as total_unit, SUM(total_bayar) as total_omset')
            ->groupBy('area_parkir_id')
            ->with('areaParkir')
            ->get();

        // 4. Performa Berdasarkan Petugas Pencatat
        $petugasReport = Transaksi::whereBetween('waktu_keluar', [$startDate, $endDate])
            ->where('status', 'selesai')
            ->selectRaw('petugas_id, COUNT(*) as total_transaksi, SUM(total_bayar) as total_setoran, 
                SUM(CASE WHEN LOWER(metode_bayar) = "tunai" THEN total_bayar ELSE 0 END) as total_tunai,
                SUM(CASE WHEN LOWER(metode_bayar) != "tunai" THEN total_bayar ELSE 0 END) as total_nontunai')
            ->groupBy('petugas_id')
            ->with('petugas')
            ->get();

        // 5. Rincian Pendapatan Harian Kalender
        $dailyReport = [];
        $daysInMonth = $endDate->day;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::createFromDate($year, $month, $d);
            if ($date->isFuture()) {
                break;
            }

            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            $masukCount = Transaksi::whereBetween('waktu_masuk', [$dayStart, $dayEnd])->count();
            $selesaiTx = Transaksi::whereBetween('waktu_keluar', [$dayStart, $dayEnd])->where('status', 'selesai');
            $keluarCount = (clone $selesaiTx)->count();
            $tunai = (clone $selesaiTx)->where('metode_bayar', 'Tunai')->sum('total_bayar');
            $qris = (clone $selesaiTx)->where('metode_bayar', '!=', 'Tunai')->sum('total_bayar');
            $omset = $tunai + $qris;

            $dailyReport[] = [
                'tanggal' => $date->translatedFormat('d M Y'),
                'hari' => $date->translatedFormat('l'),
                'masuk' => $masukCount,
                'keluar' => $keluarCount,
                'tunai' => $tunai,
                'qris' => $qris,
                'omset' => $omset,
            ];
        }

        // Balik urutan agar tanggal terbaru muncul di atas
        $dailyReport = array_reverse($dailyReport);

        return view('Owner.Laporan.index', compact(
            'month',
            'year',
            'startDate',
            'endDate',
            'totalPendapatan',
            'totalTransaksi',
            'totalSelesai',
            'avgDurasi',
            'avgPerTransaksi',
            'kendaraanReport',
            'areaReport',
            'petugasReport',
            'dailyReport'
        ));
    }

    /**
     * Ekspor Laporan Finansial Bulanan ke CSV.
     */
    public function exportLaporan(Request $request): StreamedResponse
    {
        Carbon::setLocale('id');

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $filename = 'laporan-keuangan-parkir_' . $startDate->format('F_Y') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($startDate, $endDate, $month, $year) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['LAPORAN EKSEKUTIF PENDAPATAN PARKIR - SmarPARK']);
            fputcsv($handle, ['Bulan / Tahun:', $startDate->translatedFormat('F Y')]);
            fputcsv($handle, ['Dicetak Pada:', now()->translatedFormat('d F Y H:i:s')]);
            fputcsv($handle, []);

            // Bagian 1: Rincian Harian
            fputcsv($handle, ['RINCIAN PENDAPATAN HARIAN']);
            fputcsv($handle, ['Tanggal', 'Hari', 'Kendaraan Masuk', 'Kendaraan Keluar', 'Pendapatan Tunai (Rp)', 'Pendapatan Non-Tunai/QRIS (Rp)', 'Total Omset (Rp)']);

            $daysInMonth = $endDate->day;
            $grandTunai = 0;
            $grandQris = 0;
            $grandOmset = 0;

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = Carbon::createFromDate($year, $month, $d);
                if ($date->isFuture())
                    break;

                $dayStart = $date->copy()->startOfDay();
                $dayEnd = $date->copy()->endOfDay();

                $masukCount = Transaksi::whereBetween('waktu_masuk', [$dayStart, $dayEnd])->count();
                $selesaiTx = Transaksi::whereBetween('waktu_keluar', [$dayStart, $dayEnd])->where('status', 'selesai');
                $keluarCount = (clone $selesaiTx)->count();
                $tunai = (clone $selesaiTx)->where('metode_bayar', 'Tunai')->sum('total_bayar');
                $qris = (clone $selesaiTx)->where('metode_bayar', '!=', 'Tunai')->sum('total_bayar');
                $omset = $tunai + $qris;

                $grandTunai += $tunai;
                $grandQris += $qris;
                $grandOmset += $omset;

                fputcsv($handle, [
                    $date->translatedFormat('d M Y'),
                    $date->translatedFormat('l'),
                    $masukCount,
                    $keluarCount,
                    number_format($tunai, 0, ',', '.'),
                    number_format($qris, 0, ',', '.'),
                    number_format($omset, 0, ',', '.'),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL KESELURUHAN', '', '', '', number_format($grandTunai, 0, ',', '.'), number_format($grandQris, 0, ',', '.'), number_format($grandOmset, 0, ',', '.')]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Format durasi menit ke "X jam Y mnt" atau "Y mnt".
     */
    private function formatDuration(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0 mnt';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return "{$hours} jam {$remainingMinutes} mnt";
        } elseif ($hours > 0) {
            return "{$hours} jam";
        }

        return "{$remainingMinutes} mnt";
    }
}

