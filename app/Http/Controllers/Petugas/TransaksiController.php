<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petugas\BatalkanTransaksiRequest;
use App\Http\Requests\Petugas\KendaraanKeluarRequest;
use App\Http\Requests\Petugas\KendaraanMasukRequest;
use App\Models\ActivityLog;
use App\Models\AreaParkir;
use App\Models\Tarif;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['tarif', 'areaParkir', 'petugas'])
            ->orderBy('waktu_masuk', 'desc');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('plat_nomor', 'like', "%{$search}%")
                  ->orWhere('kode_tiket', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_kendaraan', $request->jenis);
        }

        if ($request->filled('area_id')) {
            $query->where('area_parkir_id', $request->area_id);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('waktu_masuk', $request->tanggal);
        }

        $transaksis = $query->paginate(10)->withQueryString();
        $tarifs = Tarif::all();
        $areas = AreaParkir::where('status', 'aktif')->get();

        // Hitung ringkasan transaksi
        $totalSedangParkir = Transaksi::where('status', 'parkir')->count();
        $totalSelesaiHariIni = Transaksi::whereDate('waktu_keluar', now()->toDateString())
            ->where('status', 'selesai')
            ->count();

        return view('Petugas.Transaksi.index', compact(
            'transaksis',
            'tarifs',
            'areas',
            'totalSedangParkir',
            'totalSelesaiHariIni'
        ));
    }

    public function store(KendaraanMasukRequest $request)
    {
        try {
            $transaksi = DB::transaction(function () use ($request) {
                // Kunci baris area parkir dengan lockForUpdate untuk mencegah over-capacity konkuren
                $area = AreaParkir::where('id', $request->area_parkir_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($area->status !== 'aktif' || $area->slot_tersedia <= 0) {
                    throw new \Exception("Area parkir {$area->nama_area} sudah penuh atau tidak aktif.");
                }

                $tarif = Tarif::where('id', $request->tarif_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Format kode tiket otomatis: SP-YYYYMMDD-XXXX (nomor urut harian)
                $todayStr = date('Ymd');
                $latestTiket = Transaksi::where('kode_tiket', 'like', "SP-{$todayStr}-%")
                    ->latest('id')
                    ->lockForUpdate()
                    ->value('kode_tiket');

                $urutan = 1;
                if ($latestTiket && preg_match('/SP-\d{8}-(\d{4})/', $latestTiket, $matches)) {
                    $urutan = intval($matches[1]) + 1;
                }

                do {
                    $kodeTiket = 'SP-' . $todayStr . '-' . str_pad($urutan, 4, '0', STR_PAD_LEFT);
                    $exists = Transaksi::where('kode_tiket', $kodeTiket)->exists();
                    if ($exists) {
                        $urutan++;
                    }
                } while ($exists);

                // Buat snapshot tarif saat kendaraan masuk agar perubahan tarif admin di masa depan tidak mempengaruhi transaksi lama
                $tarifSnapshot = [
                    'jenis_kendaraan' => $tarif->jenis_kendaraan,
                    'tarif_per_jam' => (float) $tarif->tarif_per_jam,
                    'tarif_pertama' => $tarif->tarif_pertama !== null ? (float) $tarif->tarif_pertama : (float) $tarif->tarif_per_jam,
                    'tarif_maksimal' => $tarif->tarif_maksimal !== null ? (float) $tarif->tarif_maksimal : null,
                ];

                $tx = Transaksi::create([
                    'kode_tiket' => $kodeTiket,
                    'plat_nomor' => $request->plat_nomor,
                    'jenis_kendaraan' => $tarif->jenis_kendaraan,
                    'tarif_id' => $tarif->id,
                    'area_parkir_id' => $area->id,
                    'tarif_snapshot' => $tarifSnapshot,
                    'waktu_masuk' => now(),
                    'status' => 'parkir',
                    'petugas_id' => auth()->id(),
                ]);

                // Catat di log aktivitas
                ActivityLog::record(
                    'Kendaraan Masuk',
                    "Petugas " . auth()->user()->name . " mencatat kendaraan masuk plat {$tx->plat_nomor} ({$tx->jenis_kendaraan}) di {$area->nama_area}. Tiket: {$tx->kode_tiket}.",
                    auth()->id()
                );

                return $tx;
            });

            return redirect()->route('petugas.transaksi.index')
                ->with('success', "Kendaraan {$transaksi->plat_nomor} berhasil masuk! Tiket: {$transaksi->kode_tiket}.")
                ->with('ticket_masuk_id', $transaksi->id);

        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mencatat kendaraan masuk: ' . $e->getMessage())->withInput();
        }
    }

    public function checkout(KendaraanKeluarRequest $request, Transaksi $transaksi)
    {
        if ($transaksi->status === 'selesai') {
            return back()->with('error', 'Transaksi tiket ini sudah diselesaikan sebelumnya.');
        }

        if ($transaksi->status === 'batal') {
            return back()->with('error', 'Transaksi tiket ini telah dibatalkan.');
        }

        try {
            DB::transaction(function () use ($request, $transaksi) {
                // Kunci transaksi untuk mencegah aksi keluar ganda
                $tx = Transaksi::where('id', $transaksi->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($tx->status !== 'parkir') {
                    throw new \Exception('Status transaksi telah berubah.');
                }

                // Kunci baris area parkir jika ada
                if ($tx->area_parkir_id) {
                    AreaParkir::where('id', $tx->area_parkir_id)->lockForUpdate()->first();
                }

                $waktuKeluar = now();
                $biayaInfo = $tx->hitungBiaya($waktuKeluar);
                $totalBayar = (float) $biayaInfo['total_bayar'];

                $metode = $request->metode_bayar;
                $uangDiterima = (float) $request->uang_diterima;

                if ($metode === 'Tunai') {
                    if ($uangDiterima < $totalBayar) {
                        throw new \Exception("Uang diterima (Rp " . number_format($uangDiterima, 0, ',', '.') . ") kurang dari total bayar (Rp " . number_format($totalBayar, 0, ',', '.') . ").");
                    }
                    $kembalian = $uangDiterima - $totalBayar;
                } else {
                    $uangDiterima = $totalBayar;
                    $kembalian = 0;
                }

                $tx->update([
                    'waktu_keluar' => $waktuKeluar,
                    'durasi_menit' => $biayaInfo['durasi_menit'],
                    'total_bayar' => $totalBayar,
                    'uang_diterima' => $uangDiterima,
                    'kembalian' => $kembalian,
                    'metode_bayar' => $metode,
                    'status' => 'selesai',
                    'petugas_id' => auth()->id(), // Petugas yang memproses pembayaran keluar
                ]);

                // Catat di log aktivitas
                ActivityLog::record(
                    'Kendaraan Keluar',
                    "Petugas " . auth()->user()->name . " menyelesaikan transaksi keluar tiket {$tx->kode_tiket} (Plat: {$tx->plat_nomor}). Total: Rp " . number_format($totalBayar, 0, ',', '.') . " ({$metode}).",
                    auth()->id()
                );
            });

            return redirect()->route('petugas.struk.show', $transaksi->id)
                ->with('success', "Pembayaran tiket {$transaksi->kode_tiket} berhasil! Silakan cetak struk pembayaran.");

        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses pembayaran keluar: ' . $e->getMessage());
        }
    }

    public function cancel(BatalkanTransaksiRequest $request, Transaksi $transaksi)
    {
        if ($transaksi->status !== 'parkir') {
            return back()->with('error', 'Hanya transaksi dengan status parkir aktif yang dapat dibatalkan.');
        }

        try {
            DB::transaction(function () use ($request, $transaksi) {
                $tx = Transaksi::where('id', $transaksi->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($tx->status !== 'parkir') {
                    throw new \Exception('Transaksi sudah tidak dalam status parkir.');
                }

                // Kunci area jika ada
                if ($tx->area_parkir_id) {
                    AreaParkir::where('id', $tx->area_parkir_id)->lockForUpdate()->first();
                }

                $tx->update([
                    'status' => 'batal',
                    'alasan_batal' => $request->alasan_batal,
                    'waktu_keluar' => now(),
                ]);

                ActivityLog::record(
                    'Batalkan Transaksi',
                    "Petugas " . auth()->user()->name . " membatalkan tiket {$tx->kode_tiket} (Plat: {$tx->plat_nomor}). Alasan: {$request->alasan_batal}.",
                    auth()->id()
                );
            });

            return redirect()->route('petugas.transaksi.index')
                ->with('success', "Transaksi tiket {$transaksi->kode_tiket} ({$transaksi->plat_nomor}) berhasil dibatalkan.");

        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * API pencarian cepat kendaraan parkir untuk modal Kendaraan Keluar
     */
    public function searchActive(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['success' => false, 'message' => 'Masukkan minimal 2 karakter']);
        }

        $transaksi = Transaksi::with(['tarif', 'areaParkir'])
            ->where('status', 'parkir')
            ->where(function ($query) use ($q) {
                $query->where('plat_nomor', 'like', "%{$q}%")
                      ->orWhere('kode_tiket', 'like', "%{$q}%");
            })
            ->first();

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Kendaraan dengan plat atau kode tiket tersebut tidak ditemukan atau sudah selesai.',
            ]);
        }

        $biayaInfo = $transaksi->hitungBiaya(now());
        $waktuMasuk = Carbon::parse($transaksi->waktu_masuk);
        $durasiMenit = $biayaInfo['durasi_menit'];
        $durasiJamTeks = floor($durasiMenit / 60) . ' Jam ' . ($durasiMenit % 60) . ' Menit';

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $transaksi->id,
                'kode_tiket' => $transaksi->kode_tiket,
                'plat_nomor' => $transaksi->plat_nomor,
                'jenis_kendaraan' => $transaksi->jenis_kendaraan,
                'area_nama' => $transaksi->areaParkir ? $transaksi->areaParkir->nama_area : '-',
                'waktu_masuk' => $waktuMasuk->translatedFormat('d M Y H:i'),
                'durasi_teks' => $durasiJamTeks,
                'total_bayar' => $biayaInfo['total_bayar'],
                'url' => route('petugas.transaksi.checkout', $transaksi->id),
            ]
        ]);
    }
}
