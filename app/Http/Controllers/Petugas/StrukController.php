<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class StrukController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'masuk');
        if (!in_array($type, ['masuk', 'keluar'])) {
            $type = 'masuk';
        }

        $query = Transaksi::with(['tarif', 'areaParkir', 'petugas']);

        if ($type === 'masuk') {
            // Struk Masuk: Menampilkan kendaraan yang masuk (sedang parkir atau selesai)
            $query->orderBy('waktu_masuk', 'desc');

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            } else {
                $query->whereIn('status', ['parkir', 'selesai']);
            }

            if ($request->filled('tanggal')) {
                $query->whereDate('waktu_masuk', $request->tanggal);
            }
        } else {
            // Struk Keluar: Menampilkan kendaraan yang sudah selesai checkout
            $query->where('status', 'selesai')
                  ->orderBy('waktu_keluar', 'desc');

            if ($request->filled('tanggal')) {
                $query->whereDate('waktu_keluar', $request->tanggal);
            }
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('plat_nomor', 'like', "%{$search}%")
                  ->orWhere('kode_tiket', 'like', "%{$search}%");
            });
        }

        $transaksis = $query->paginate(10)->withQueryString();

        // Data statistik / counter untuk badge tab
        $totalMasuk = Transaksi::whereIn('status', ['parkir', 'selesai'])->count();
        $totalKeluar = Transaksi::where('status', 'selesai')->count();
        $totalSedangParkir = Transaksi::where('status', 'parkir')->count();

        return view('Petugas.Struk.index', compact(
            'transaksis',
            'type',
            'totalMasuk',
            'totalKeluar',
            'totalSedangParkir'
        ));
    }

    public function masuk(Request $request)
    {
        $request->merge(['type' => 'masuk']);
        return $this->index($request);
    }

    public function keluar(Request $request)
    {
        $request->merge(['type' => 'keluar']);
        return $this->index($request);
    }

    public function show(Request $request, Transaksi $transaksi)
    {
        // Load relasi yang diperlukan
        $transaksi->load(['tarif', 'areaParkir', 'petugas']);

        // Ambil pengaturan sistem untuk kop dan footer struk
        $settings = [
            'app_name' => Setting::get('app_name', 'SmarPARK'),
            'company_name' => Setting::get('company_name', 'PT Solusi Parkir Pintar'),
            'company_address' => Setting::get('company_address', 'Jl. Merdeka No. 45, Jakarta'),
            'company_phone' => Setting::get('company_phone', '0812-3456-7890'),
            'footer_text' => Setting::get('footer_text', 'Terima kasih atas kunjungan Anda. Harap simpan bukti pembayaran ini.'),
            'default_paper_size' => Setting::get('receipt_paper_size', '80mm'),
        ];

        // Tentukan tipe struk: 'masuk' (struk masuk) atau 'keluar' (struk keluar)
        $tipe = $request->get('type', ($transaksi->status === 'parkir' ? 'masuk' : 'keluar'));

        // Catat di log aktivitas
        if ($tipe === 'masuk') {
            ActivityLog::record(
                'Cetak Struk Masuk',
                "Petugas " . auth()->user()->name . " mencetak struk masuk tiket {$transaksi->kode_tiket} (Plat: {$transaksi->plat_nomor}).",
                auth()->id()
            );
        } else {
            ActivityLog::record(
                'Cetak Struk Keluar',
                "Petugas " . auth()->user()->name . " mencetak struk keluar bukti bayar {$transaksi->kode_tiket} (Plat: {$transaksi->plat_nomor}).",
                auth()->id()
            );
        }

        return view('Petugas.Struk.print', compact('transaksi', 'settings', 'tipe'));
    }
}
