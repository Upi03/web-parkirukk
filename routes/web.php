<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('LandingPage.index');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $role = auth()->user()->role;
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'petugas') {
            return redirect()->route('petugas.dashboard');
        } elseif ($role === 'owner') {
            return redirect()->route('owner.dashboard');
        }
        return inertia('dashboard');
    })->name('dashboard');

    // Quick Search Global (Ctrl + K) untuk semua role yang terautentikasi
    Route::get('/quick-search', function (\Illuminate\Http\Request $request) {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $user = auth()->user();
        $isPetugas = $user && $user->role === 'petugas';
        $results = [];

        // Pencarian Kendaraan / Transaksi
        $kendaraans = \App\Models\Transaksi::where('plat_nomor', 'like', "%{$q}%")
            ->orWhere('kode_tiket', 'like', "%{$q}%")
            ->take(5)
            ->get();

        foreach ($kendaraans as $k) {
            $targetUrl = $isPetugas
                ? route('petugas.transaksi.index', ['search' => $k->plat_nomor])
                : route('admin.kendaraan.index', ['search' => $k->plat_nomor]);

            $results[] = [
                'category' => 'Kendaraan',
                'title' => $k->plat_nomor . ' (' . ucfirst($k->status) . ')',
                'subtitle' => $k->kode_tiket . ' &bull; ' . $k->jenis_kendaraan . ($k->areaParkir ? ' &bull; ' . $k->areaParkir->nama_area : ''),
                'url' => $targetUrl,
                'icon' => 'car',
            ];
        }

        // Pencarian Area Parkir
        $areas = \App\Models\AreaParkir::where('nama_area', 'like', "%{$q}%")
            ->take(3)
            ->get();

        foreach ($areas as $a) {
            $targetUrl = $isPetugas
                ? route('petugas.transaksi.index', ['area_id' => $a->id])
                : route('admin.area.index', ['search' => $a->nama_area]);

            $results[] = [
                'category' => 'Area Parkir',
                'title' => $a->nama_area,
                'subtitle' => 'Kapasitas ' . $a->kapasitas_total . ' slot (Sisa ' . $a->slot_tersedia . ')',
                'url' => $targetUrl,
                'icon' => 'map-pin',
            ];
        }

        // Pencarian User hanya untuk Admin
        if (! $isPetugas) {
            $users = \App\Models\User::where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->take(3)
                ->get();

            foreach ($users as $u) {
                $results[] = [
                    'category' => 'User',
                    'title' => $u->name,
                    'subtitle' => $u->email . ' (' . ucfirst($u->role) . ')',
                    'url' => route('admin.users.index', ['search' => $u->name]),
                    'icon' => 'users',
                ];
            }
        }

        return response()->json(['results' => $results]);
    })->name('quick-search');
});

// ==========================================
// RUTE KHUSUS ROLE PETUGAS
// ==========================================
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Petugas\DashboardController::class, 'index'])->name('dashboard');

    // Transaksi Parkir
    Route::get('/transaksi', [\App\Http\Controllers\Petugas\TransaksiController::class, 'index'])->name('transaksi.index');
    Route::post('/transaksi', [\App\Http\Controllers\Petugas\TransaksiController::class, 'store'])->name('transaksi.store');
    Route::patch('/transaksi/{transaksi}/keluar', [\App\Http\Controllers\Petugas\TransaksiController::class, 'checkout'])->name('transaksi.checkout');
    Route::post('/transaksi/{transaksi}/batal', [\App\Http\Controllers\Petugas\TransaksiController::class, 'cancel'])->name('transaksi.cancel');
    Route::get('/transaksi/cek-parkir', [\App\Http\Controllers\Petugas\TransaksiController::class, 'searchActive'])->name('transaksi.cek-parkir');

    // Cetak Struk (Masuk & Keluar)
    Route::get('/struk', [\App\Http\Controllers\Petugas\StrukController::class, 'index'])->name('struk.index');
    Route::get('/struk/masuk', [\App\Http\Controllers\Petugas\StrukController::class, 'masuk'])->name('struk.masuk');
    Route::get('/struk/keluar', [\App\Http\Controllers\Petugas\StrukController::class, 'keluar'])->name('struk.keluar');
    Route::get('/struk/{transaksi}', [\App\Http\Controllers\Petugas\StrukController::class, 'show'])->name('struk.show');

    // Profil Petugas
    Route::get('/profil', [\App\Http\Controllers\Petugas\ProfilController::class, 'index'])->name('profil.index');
    Route::patch('/profil', [\App\Http\Controllers\Petugas\ProfilController::class, 'updateProfile'])->name('profil.update');
    Route::put('/profil/password', [\App\Http\Controllers\Petugas\ProfilController::class, 'updatePassword'])->name('profil.password');

    // Pengaturan Operasional Petugas
    Route::get('/pengaturan', [\App\Http\Controllers\Petugas\PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan', [\App\Http\Controllers\Petugas\PengaturanController::class, 'update'])->name('pengaturan.update');
});

// ==========================================
// RUTE KHUSUS ROLE ADMIN
// ==========================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Manajemen User
    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/users/{user}/toggle-status', [\App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    
    // Tarif Parkir
    Route::get('/tarif', [\App\Http\Controllers\Admin\TarifController::class, 'index'])->name('tarif.index');
    Route::post('/tarif', [\App\Http\Controllers\Admin\TarifController::class, 'store'])->name('tarif.store');
    Route::put('/tarif/{tarif}', [\App\Http\Controllers\Admin\TarifController::class, 'update'])->name('tarif.update');
    Route::delete('/tarif/{tarif}', [\App\Http\Controllers\Admin\TarifController::class, 'destroy'])->name('tarif.destroy');
    
    // Area Parkir
    Route::get('/area', [\App\Http\Controllers\Admin\AreaParkirController::class, 'index'])->name('area.index');
    Route::post('/area', [\App\Http\Controllers\Admin\AreaParkirController::class, 'store'])->name('area.store');
    Route::put('/area/{area}', [\App\Http\Controllers\Admin\AreaParkirController::class, 'update'])->name('area.update');
    Route::delete('/area/{area}', [\App\Http\Controllers\Admin\AreaParkirController::class, 'destroy'])->name('area.destroy');
    
    // Kendaraan (Monitoring & Audit Transaksi)
    Route::get('/kendaraan', [\App\Http\Controllers\Admin\KendaraanController::class, 'index'])->name('kendaraan.index');
    Route::put('/kendaraan/{kendaraan}', [\App\Http\Controllers\Admin\KendaraanController::class, 'update'])->name('kendaraan.update');
    Route::delete('/kendaraan/{kendaraan}', [\App\Http\Controllers\Admin\KendaraanController::class, 'destroy'])->name('kendaraan.destroy');
    
    // Log Aktivitas
    Route::get('/log', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('log.index');

    // Profil Admin
    Route::get('/profil', [\App\Http\Controllers\Admin\ProfilController::class, 'index'])->name('profil.index');
    Route::patch('/profil', [\App\Http\Controllers\Admin\ProfilController::class, 'updateProfile'])->name('profil.update');
    Route::put('/profil/password', [\App\Http\Controllers\Admin\ProfilController::class, 'updatePassword'])->name('profil.password');

    // Pengaturan Sistem
    Route::get('/pengaturan', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('pengaturan.update');

    // Backward-compatibility alias untuk search admin
    Route::get('/search', function (\Illuminate\Http\Request $request) {
        return redirect()->route('quick-search', $request->query());
    })->name('search');
});

// ==========================================
// RUTE KHUSUS ROLE OWNER
// ==========================================
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Owner\OwnerController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/data', [\App\Http\Controllers\Owner\OwnerController::class, 'data'])->name('dashboard.data');
    Route::get('/dashboard/export', [\App\Http\Controllers\Owner\OwnerController::class, 'export'])->name('dashboard.export');

    // Rekap Transaksi
    Route::get('/transaksi', [\App\Http\Controllers\Owner\OwnerController::class, 'transaksi'])->name('transaksi.index');
    Route::get('/transaksi/{transaksi}', [\App\Http\Controllers\Owner\OwnerController::class, 'transaksiDetail'])->name('transaksi.show');

    // Laporan Finansial & Operasional
    Route::get('/laporan', [\App\Http\Controllers\Owner\OwnerController::class, 'laporan'])->name('laporan.index');
    Route::get('/laporan/export', [\App\Http\Controllers\Owner\OwnerController::class, 'exportLaporan'])->name('laporan.export');
});

require __DIR__ . '/settings.php';