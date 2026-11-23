<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateKendaraanRequest;
use App\Models\ActivityLog;
use App\Models\AreaParkir;
use App\Models\Tarif;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KendaraanController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaksi::with(['tarif', 'areaParkir', 'petugas'])
            ->orderBy('created_at', 'desc');

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

        $kendaraans = $query->paginate(10)->withQueryString();
        $tarifs = Tarif::all();
        $areas = AreaParkir::where('status', 'aktif')->get();

        // Statistik ringkas monitoring untuk admin
        $totalKendaraan = Transaksi::count();
        $totalParkir = Transaksi::where('status', 'parkir')->count();
        $totalSelesaiHariIni = Transaksi::whereDate('waktu_keluar', now()->toDateString())
            ->where('status', 'selesai')
            ->count();

        return view('Admin.Kendaraan.index', compact(
            'kendaraans',
            'tarifs',
            'areas',
            'totalKendaraan',
            'totalParkir',
            'totalSelesaiHariIni'
        ));
    }

    public function update(UpdateKendaraanRequest $request, Transaksi $kendaraan)
    {
        $tarif = Tarif::findOrFail($request->tarif_id);
        $oldPlat = $kendaraan->plat_nomor;

        $kendaraan->update([
            'plat_nomor' => strtoupper(trim($request->plat_nomor)),
            'jenis_kendaraan' => $tarif->jenis_kendaraan,
            'tarif_id' => $tarif->id,
            'area_parkir_id' => $request->area_parkir_id,
            'status' => $request->status,
        ]);

        ActivityLog::record(
            'Edit Kendaraan',
            "Admin " . auth()->user()->name . " memperbarui data kendaraan: {$oldPlat} -> {$kendaraan->plat_nomor}."
        );

        return redirect()->route('admin.kendaraan.index')->with('success', "Data kendaraan {$kendaraan->plat_nomor} berhasil diperbarui.");
    }

    public function destroy(Transaksi $kendaraan)
    {
        $plat = $kendaraan->plat_nomor;
        $kode = $kendaraan->kode_tiket;
        $kendaraan->delete();

        ActivityLog::record(
            'Hapus Transaksi Kendaraan',
            "Admin " . auth()->user()->name . " menghapus transaksi kendaraan plat {$plat} ({$kode})."
        );

        return redirect()->route('admin.kendaraan.index')->with('success', "Data transaksi {$plat} berhasil dihapus.");
    }
}
