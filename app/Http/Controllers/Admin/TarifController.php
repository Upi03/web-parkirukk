<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTarifRequest;
use App\Http\Requests\Admin\UpdateTarifRequest;
use App\Models\ActivityLog;
use App\Models\Tarif;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class TarifController extends Controller
{
    public function index(Request $request)
    {
        $query = Tarif::query();

        if ($request->filled('search')) {
            $query->where('jenis_kendaraan', 'like', '%' . $request->search . '%');
        }

        $tarifs = $query->orderBy('jenis_kendaraan')->paginate(10)->withQueryString();

        return view('Admin.Tarif.index', compact('tarifs'));
    }

    public function store(StoreTarifRequest $request)
    {
        $tarif = Tarif::create($request->validated());

        ActivityLog::record(
            'Tambah Tarif',
            "Admin " . auth()->user()->name . " menambahkan tarif baru untuk {$tarif->jenis_kendaraan} (Rp " . number_format($tarif->tarif_per_jam, 0, ',', '.') . "/jam)."
        );

        return redirect()->route('admin.tarif.index')->with('success', "Tarif untuk {$tarif->jenis_kendaraan} berhasil ditambahkan.");
    }

    public function update(UpdateTarifRequest $request, Tarif $tarif)
    {
        $oldJenis = $tarif->jenis_kendaraan;
        $tarif->update($request->validated());

        ActivityLog::record(
            'Edit Tarif',
            "Admin " . auth()->user()->name . " memperbarui tarif {$oldJenis} (Tarif per jam: Rp " . number_format($tarif->tarif_per_jam, 0, ',', '.') . ")."
        );

        return redirect()->route('admin.tarif.index')->with('success', "Tarif {$tarif->jenis_kendaraan} berhasil diperbarui.");
    }

    public function destroy(Tarif $tarif)
    {
        $masihDigunakan = Transaksi::where('tarif_id', $tarif->id)
            ->where('status', 'parkir')
            ->exists();

        if ($masihDigunakan) {
            return redirect()->route('admin.tarif.index')->with('error', "Tarif {$tarif->jenis_kendaraan} tidak dapat dihapus karena masih digunakan oleh kendaraan yang sedang parkir.");
        }

        $jenis = $tarif->jenis_kendaraan;
        $tarif->delete();

        ActivityLog::record(
            'Hapus Tarif',
            "Admin " . auth()->user()->name . " menghapus tarif jenis kendaraan: {$jenis}."
        );

        return redirect()->route('admin.tarif.index')->with('success', "Tarif {$jenis} berhasil dihapus.");
    }
}
