<?php

namespace Database\Seeders;

use App\Models\AreaParkir;
use App\Models\Tarif;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan Akun Owner Ada & Aktif
        $owner = User::firstOrCreate(
            ['email' => 'owner@smarpark.local'],
            [
                'name' => 'Bpk. Hendra Gunawan (Owner)',
                'password' => Hash::make('owner123'),
                'role' => 'owner',
                'is_active' => true,
            ]
        );

        // Ambil Petugas untuk relasi petugas_id
        $petugasList = User::where('role', 'petugas')->get();
        if ($petugasList->isEmpty()) {
            $petugasList = collect([
                User::create([
                    'name' => 'Budi Santoso',
                    'email' => 'petugas@smarpark.local',
                    'password' => Hash::make('petugas123'),
                    'role' => 'petugas',
                    'is_active' => true,
                ]),
                User::create([
                    'name' => 'Siti Aminah',
                    'email' => 'siti@smarpark.local',
                    'password' => Hash::make('password123'),
                    'role' => 'petugas',
                    'is_active' => true,
                ]),
            ]);
        }

        // Ambil atau buat Tarif
        $tarifMobil = Tarif::firstOrCreate(
            ['jenis_kendaraan' => 'Mobil'],
            ['tarif_per_jam' => 5000, 'tarif_pertama' => 5000, 'tarif_maksimal' => 40000]
        );
        $tarifMotor = Tarif::firstOrCreate(
            ['jenis_kendaraan' => 'Motor'],
            ['tarif_per_jam' => 2000, 'tarif_pertama' => 2000, 'tarif_maksimal' => 15000]
        );
        $tarifTruk = Tarif::firstOrCreate(
            ['jenis_kendaraan' => 'Truk / Bus'],
            ['tarif_per_jam' => 10000, 'tarif_pertama' => 12000, 'tarif_maksimal' => 80000]
        );

        // Ambil atau buat Area Parkir
        $areaDepan = AreaParkir::firstOrCreate(
            ['nama_area' => 'Lantai 1 - Depan'],
            ['jenis_kendaraan' => 'Mobil', 'kapasitas_total' => 30, 'status' => 'aktif']
        );
        $areaMotor = AreaParkir::firstOrCreate(
            ['nama_area' => 'Basement - Motor'],
            ['jenis_kendaraan' => 'Motor', 'kapasitas_total' => 100, 'status' => 'aktif']
        );
        $areaMobilLt2 = AreaParkir::firstOrCreate(
            ['nama_area' => 'Lantai 2 - Mobil'],
            ['jenis_kendaraan' => 'Mobil', 'kapasitas_total' => 45, 'status' => 'aktif']
        );

        $platPrefixes = ['B', 'D', 'F', 'L', 'AB', 'AD', 'H', 'A'];
        $platLetters = ['ABC', 'XYZ', 'KAA', 'DEF', 'PRO', 'BOS', 'VIP', 'MTR', 'SMK', 'TRK', 'JKT', 'BDG'];
        $metodeBayarList = ['Tunai', 'QRIS', 'QRIS', 'Tunai', 'Transfer'];

        // 2. Generate transaksi selama 30 hari ke belakang sampai hari ini
        for ($daysAgo = 30; $daysAgo >= 0; $daysAgo--) {
            $currentDay = now()->subDays($daysAgo);
            $dateStr = $currentDay->format('Ymd');

            // Hari ini atau weekend punya jumlah kendaraan lebih banyak
            $isWeekend = $currentDay->isWeekend();
            $countForDay = $daysAgo === 0 ? 12 : ($isWeekend ? rand(10, 16) : rand(6, 12));

            for ($i = 1; $i <= $countForDay; $i++) {
                $ticketCode = sprintf('SP-%s-%04d', $dateStr, $i + 100);

                // Cek apakah sudah pernah ada tiket ini
                if (Transaksi::where('kode_tiket', $ticketCode)->exists()) {
                    continue;
                }

                // Random jenis kendaraan (50% Mobil, 40% Motor, 10% Truk)
                $rnd = rand(1, 100);
                if ($rnd <= 50) {
                    $jenis = 'Mobil';
                    $tarif = $tarifMobil;
                    $area = rand(1, 2) === 1 ? $areaDepan : $areaMobilLt2;
                } elseif ($rnd <= 90) {
                    $jenis = 'Motor';
                    $tarif = $tarifMotor;
                    $area = $areaMotor;
                } else {
                    $jenis = 'Truk / Bus';
                    $tarif = $tarifTruk;
                    $area = $areaDepan;
                }

                // Waktu masuk antara 06:00 sampai 21:00
                $hourIn = rand(6, 20);
                $minuteIn = rand(0, 59);
                $waktuMasuk = $currentDay->copy()->startOfDay()->addHours($hourIn)->addMinutes($minuteIn);

                // Durasi antara 30 menit sampai 360 menit (6 jam)
                $durasiMenit = rand(25, 360);
                $waktuKeluar = $waktuMasuk->copy()->addMinutes($durasiMenit);

                // Status transaksi
                // Jika hari ini dan jam masuk > 10, beberapa masih berstatus "parkir"
                $status = 'selesai';
                if ($daysAgo === 0 && $i % 4 === 0) {
                    $status = 'parkir';
                    $waktuKeluar = null;
                    $durasiMenit = null;
                    $totalBayar = null;
                    $uangDiterima = null;
                    $kembalian = null;
                    $metode = null;
                } else {
                    // Hitung total bayar berdasarkan tarif
                    $durasiJam = (int) ceil($durasiMenit / 60);
                    $totalBayar = (float) ($tarif->tarif_pertama + max(0, $durasiJam - 1) * $tarif->tarif_per_jam);
                    if ($tarif->tarif_maksimal && $totalBayar > $tarif->tarif_maksimal) {
                        $totalBayar = (float) $tarif->tarif_maksimal;
                    }

                    $metode = $metodeBayarList[array_rand($metodeBayarList)];
                    if ($metode === 'Tunai') {
                        // Pembulatan ke nominal uang kertas
                        $uangDiterima = ceil($totalBayar / 10000) * 10000;
                        if ($uangDiterima < $totalBayar) $uangDiterima = $totalBayar;
                        $kembalian = $uangDiterima - $totalBayar;
                    } else {
                        $uangDiterima = $totalBayar;
                        $kembalian = 0;
                    }
                }

                $prefix = $platPrefixes[array_rand($platPrefixes)];
                $suffix = $platLetters[array_rand($platLetters)];
                $platNomor = $prefix . ' ' . rand(1000, 9999) . ' ' . $suffix;
                $petugas = $petugasList->random();

                Transaksi::create([
                    'kode_tiket' => $ticketCode,
                    'plat_nomor' => $platNomor,
                    'jenis_kendaraan' => $jenis,
                    'tarif_id' => $tarif->id,
                    'area_parkir_id' => $area->id,
                    'waktu_masuk' => $waktuMasuk,
                    'waktu_keluar' => $waktuKeluar,
                    'durasi_menit' => $durasiMenit,
                    'total_bayar' => $totalBayar,
                    'uang_diterima' => $uangDiterima,
                    'kembalian' => $kembalian,
                    'metode_bayar' => $metode,
                    'status' => $status,
                    'petugas_id' => $petugas->id,
                    'created_at' => $waktuMasuk,
                    'updated_at' => $waktuKeluar ?? $waktuMasuk,
                ]);
            }
        }
    }
}
