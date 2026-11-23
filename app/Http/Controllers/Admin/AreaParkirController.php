<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAreaParkirRequest;
use App\Http\Requests\Admin\UpdateAreaParkirRequest;
use App\Models\ActivityLog;
use App\Models\AreaParkir;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class AreaParkirController extends Controller
{
    public function index(Request $request)
    {
        $query = AreaParkir::query();

        if ($request->filled('search')) {
            $query->where('nama_area', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $areas = $query->latest()->paginate(10)->withQueryString();

        return view('Admin.Area.index', compact('areas'));
    }

    public function store(StoreAreaParkirRequest $request)
    {
        $area = AreaParkir::create($request->validated());

        ActivityLog::record(
            'Tambah Area Parkir',
            "Admin " . auth()->user()->name . " menambahkan area parkir baru: {$area->nama_area} (Kapasitas: {$area->kapasitas_total} slot)."
        );

        return redirect()->route('admin.area.index')->with('success', "Area parkir {$area->nama_area} berhasil ditambahkan.");
    }

    public function update(UpdateAreaParkirRequest $request, AreaParkir $area)
    {
        $oldNama = $area->nama_area;
        $area->update($request->validated());

        ActivityLog::record(
            'Edit Area Parkir',
            "Admin " . auth()->user()->name . " memperbarui area parkir {$oldNama} (Kapasitas: {$area->kapasitas_total} slot, Status: {$area->status})."
        );

        return redirect()->route('admin.area.index')->with('success', "Area parkir {$area->nama_area} berhasil diperbarui.");
    }

    public function destroy(AreaParkir $area)
    {
        $sedangParkir = Transaksi::where('area_parkir_id', $area->id)
            ->where('status', 'parkir')
            ->count();

        if ($sedangParkir > 0) {
            return redirect()->route('admin.area.index')->with('error', "Area {$area->nama_area} tidak dapat dihapus karena masih ada {$sedangParkir} kendaraan yang sedang parkir.");
        }

        $adaRiwayat = Transaksi::where('area_parkir_id', $area->id)->exists();
        if ($adaRiwayat) {
            return redirect()->route('admin.area.index')->with('error', "Area {$area->nama_area} memiliki riwayat transaksi parkir. Untuk menjaga keutuhan laporan, silakan ubah status menjadi Nonaktif alih-alih menghapusnya.");
        }

        $namaArea = $area->nama_area;
        $area->delete();

        ActivityLog::record(
            'Hapus Area Parkir',
            "Admin " . auth()->user()->name . " menghapus area parkir: {$namaArea}."
        );

        return redirect()->route('admin.area.index')->with('success', "Area parkir {$namaArea} berhasil dihapus.");
    }
}
