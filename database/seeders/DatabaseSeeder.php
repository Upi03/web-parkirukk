<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\AreaParkir;
use App\Models\Setting;
use App\Models\Tarif;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna
        $admin = User::firstOrCreate(
            ['email' => 'admin@smarpark.local'],
            [
                'name' => 'Administrator Utama',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $petugas1 = User::firstOrCreate(
            ['email' => 'petugas@smarpark.local'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('petugas123'),
                'role' => 'petugas',
                'is_active' => true,
            ]
        );

        $petugas2 = User::firstOrCreate(
            ['email' => 'siti@smarpark.local'],
            [
                'name' => 'Siti Aminah',
                'password' => Hash::make('password123'),
                'role' => 'petugas',
                'is_active' => true,
            ]
        );

        $petugasNonaktif = User::firstOrCreate(
            ['email' => 'andi@smarpark.local'],
            [
                'name' => 'Andi Pratama',
                'password' => Hash::make('password123'),
                'role' => 'petugas',
                'is_active' => false,
            ]
        );

        $owner = User::firstOrCreate(
            ['email' => 'owner@smarpark.local'],
            [
                'name' => 'H. Suryanto (Owner)',
                'password' => Hash::make('owner123'),
                'role' => 'owner',
                'is_active' => true,
            ]
        );

        // 2. Data Area Parkir
        $areaDepan = AreaParkir::firstOrCreate(
            ['nama_area' => 'Lantai 1 - Depan'],
            [
                'jenis_kendaraan' => 'Mobil',
                'kapasitas_total' => 30,
                'keterangan' => 'Khusus untuk mobil tamu VIP, drop-off, dan staf manajemen.',
                'status' => 'aktif',
            ]
        );

        $areaMotor = AreaParkir::firstOrCreate(
            ['nama_area' => 'Basement - Motor'],
            [
                'jenis_kendaraan' => 'Motor',
                'kapasitas_total' => 100,
                'keterangan' => 'Area parkir roda dua umum dengan pengawasan CCTV 24 jam.',
                'status' => 'aktif',
            ]
        );

        $areaMobilLt2 = AreaParkir::firstOrCreate(
            ['nama_area' => 'Lantai 2 - Mobil'],
            [
                'jenis_kendaraan' => 'Mobil',
                'kapasitas_total' => 45,
                'keterangan' => 'Area parkir kendaraan roda empat umum.',
                'status' => 'aktif',
            ]
        );

        $areaRenov = AreaParkir::firstOrCreate(
            ['nama_area' => 'Outdoor Timur (Renovasi)'],
            [
                'jenis_kendaraan' => 'Mobil',
                'kapasitas_total' => 15,
                'keterangan' => 'Dalam proses pemasangan kanopi pelindung matahari.',
                'status' => 'nonaktif',
            ]
        );

        // 3. Data Tarif Parkir
        $tarifMobil = Tarif::firstOrCreate(
            ['jenis_kendaraan' => 'Mobil'],
            [
                'tarif_per_jam' => 5000,
                'tarif_pertama' => 5000,
                'tarif_maksimal' => 40000,
            ]
        );

        $tarifMotor = Tarif::firstOrCreate(
            ['jenis_kendaraan' => 'Motor'],
            [
                'tarif_per_jam' => 2000,
                'tarif_pertama' => 2000,
                'tarif_maksimal' => 15000,
            ]
        );

        $tarifTruk = Tarif::firstOrCreate(
            ['jenis_kendaraan' => 'Truk / Bus'],
            [
                'tarif_per_jam' => 10000,
                'tarif_pertama' => 12000,
                'tarif_maksimal' => 80000,
            ]
        );

        // 4. Data Transaksi Parkir
        $today = now();

        // 4a. Transaksi yang sedang PARKIR hari ini
        $parkirData = [
            [
                'kode_tiket' => 'SP-' . date('Ymd') . '-0001',
                'plat_nomor' => 'B 1928 XYZ',
                'jenis_kendaraan' => 'Mobil',
                'tarif_id' => $tarifMobil->id,
                'area_parkir_id' => $areaDepan->id,
                'waktu_masuk' => now()->startOfDay()->addHours(8)->addMinutes(15),
                'status' => 'parkir',
                'petugas_id' => $petugas1->id,
            ],
            [
                'kode_tiket' => 'SP-' . date('Ymd') . '-0002',
                'plat_nomor' => 'D 4567 KAA',
                'jenis_kendaraan' => 'Motor',
                'tarif_id' => $tarifMotor->id,
                'area_parkir_id' => $areaMotor->id,
                'waktu_masuk' => now()->startOfDay()->addHours(9)->addMinutes(30),
                'status' => 'parkir',
                'petugas_id' => $petugas1->id,
            ],
            [
                'kode_tiket' => 'SP-' . date('Ymd') . '-0003',
                'plat_nomor' => 'B 8888 BOS',
                'jenis_kendaraan' => 'Mobil',
                'tarif_id' => $tarifMobil->id,
                'area_parkir_id' => $areaDepan->id,
                'waktu_masuk' => now()->startOfDay()->addHours(10)->addMinutes(5),
                'status' => 'parkir',
                'petugas_id' => $petugas2->id,
            ],
            [
                'kode_tiket' => 'SP-' . date('Ymd') . '-0004',
                'plat_nomor' => 'F 1029 DEF',
                'jenis_kendaraan' => 'Mobil',
                'tarif_id' => $tarifMobil->id,
                'area_parkir_id' => $areaMobilLt2->id,
                'waktu_masuk' => now()->startOfDay()->addHours(11)->addMinutes(45),
                'status' => 'parkir',
                'petugas_id' => $petugas2->id,
            ],
            [
                'kode_tiket' => 'SP-' . date('Ymd') . '-0005',
                'plat_nomor' => 'B 3012 TAA',
                'jenis_kendaraan' => 'Motor',
                'tarif_id' => $tarifMotor->id,
                'area_parkir_id' => $areaMotor->id,
                'waktu_masuk' => now()->startOfDay()->addHours(12)->addMinutes(10),
                'status' => 'parkir',
                'petugas_id' => $petugas1->id,
            ],
        ];

        foreach ($parkirData as $item) {
            Transaksi::firstOrCreate(['kode_tiket' => $item['kode_tiket']], $item);
        }

        // 4b. Transaksi SELESAI hari ini (untuk grafik kendaraan per jam & pendapatan hari ini)
        $selesaiHariIni = [
            [
                'kode_tiket' => 'SP-' . date('Ymd') . '-0006',
                'plat_nomor' => 'B 7721 ZAA',
                'jenis_kendaraan' => 'Mobil',
                'tarif_id' => $tarifMobil->id,
                'area_parkir_id' => $areaMobilLt2->id,
                'waktu_masuk' => now()->startOfDay()->addHours(7)->addMinutes(10),
                'waktu_keluar' => now()->startOfDay()->addHours(9)->addMinutes(15),
                'durasi_menit' => 125,
                'total_bayar' => 15000,
                'uang_diterima' => 20000,
                'kembalian' => 5000,
                'metode_bayar' => 'Tunai',
                'status' => 'selesai',
                'petugas_id' => $petugas1->id,
            ],
            [
                'kode_tiket' => 'SP-' . date('Ymd') . '-0007',
                'plat_nomor' => 'B 3490 QWE',
                'jenis_kendaraan' => 'Motor',
                'tarif_id' => $tarifMotor->id,
                'area_parkir_id' => $areaMotor->id,
                'waktu_masuk' => now()->startOfDay()->addHours(8)->addMinutes(50),
                'waktu_keluar' => now()->startOfDay()->addHours(10)->addMinutes(20),
                'durasi_menit' => 90,
                'total_bayar' => 4000,
                'uang_diterima' => 5000,
                'kembalian' => 1000,
                'metode_bayar' => 'Tunai',
                'status' => 'selesai',
                'petugas_id' => $petugas1->id,
            ],
            [
                'kode_tiket' => 'SP-' . date('Ymd') . '-0008',
                'plat_nomor' => 'D 9912 JKL',
                'jenis_kendaraan' => 'Mobil',
                'tarif_id' => $tarifMobil->id,
                'area_parkir_id' => $areaDepan->id,
                'waktu_masuk' => now()->startOfDay()->addHours(10)->addMinutes(30),
                'waktu_keluar' => now()->startOfDay()->addHours(12)->addMinutes(30),
                'durasi_menit' => 120,
                'total_bayar' => 10000,
                'uang_diterima' => 10000,
                'kembalian' => 0,
                'metode_bayar' => 'QRIS',
                'status' => 'selesai',
                'petugas_id' => $petugas2->id,
            ],
            [
                'kode_tiket' => 'SP-' . date('Ymd') . '-0009',
                'plat_nomor' => 'B 4410 POL',
                'jenis_kendaraan' => 'Truk / Bus',
                'tarif_id' => $tarifTruk->id,
                'area_parkir_id' => $areaDepan->id,
                'waktu_masuk' => now()->startOfDay()->addHours(9)->addMinutes(0),
                'waktu_keluar' => now()->startOfDay()->addHours(11)->addMinutes(30),
                'durasi_menit' => 150,
                'total_bayar' => 32000,
                'uang_diterima' => 50000,
                'kembalian' => 18000,
                'metode_bayar' => 'Tunai',
                'status' => 'selesai',
                'petugas_id' => $petugas1->id,
            ],
        ];

        foreach ($selesaiHariIni as $item) {
            Transaksi::firstOrCreate(['kode_tiket' => $item['kode_tiket']], $item);
        }

        // 4c. Transaksi 6 hari lalu untuk grafik pendapatan 7 hari terakhir
        for ($day = 6; $day >= 1; $day--) {
            $pastDate = now()->subDays($day);
            $dateStr = $pastDate->format('Ymd');
            
            // Tambahkan 3 transaksi selesai per hari lampau
            Transaksi::firstOrCreate(
                ['kode_tiket' => 'SP-' . $dateStr . '-0001'],
                [
                    'plat_nomor' => 'B ' . rand(1000, 9999) . ' ABC',
                    'jenis_kendaraan' => 'Mobil',
                    'tarif_id' => $tarifMobil->id,
                    'area_parkir_id' => $areaMobilLt2->id,
                    'waktu_masuk' => $pastDate->copy()->startOfDay()->addHours(9),
                    'waktu_keluar' => $pastDate->copy()->startOfDay()->addHours(13),
                    'durasi_menit' => 240,
                    'total_bayar' => 20000,
                    'uang_diterima' => 20000,
                    'kembalian' => 0,
                    'metode_bayar' => 'Tunai',
                    'status' => 'selesai',
                    'petugas_id' => $petugas1->id,
                ]
            );

            Transaksi::firstOrCreate(
                ['kode_tiket' => 'SP-' . $dateStr . '-0002'],
                [
                    'plat_nomor' => 'D ' . rand(1000, 9999) . ' XYZ',
                    'jenis_kendaraan' => 'Motor',
                    'tarif_id' => $tarifMotor->id,
                    'area_parkir_id' => $areaMotor->id,
                    'waktu_masuk' => $pastDate->copy()->startOfDay()->addHours(10),
                    'waktu_keluar' => $pastDate->copy()->startOfDay()->addHours(14),
                    'durasi_menit' => 240,
                    'total_bayar' => 8000,
                    'uang_diterima' => 10000,
                    'kembalian' => 2000,
                    'metode_bayar' => 'QRIS',
                    'status' => 'selesai',
                    'petugas_id' => $petugas2->id,
                ]
            );

            Transaksi::firstOrCreate(
                ['kode_tiket' => 'SP-' . $dateStr . '-0003'],
                [
                    'plat_nomor' => 'B ' . rand(1000, 9999) . ' PRO',
                    'jenis_kendaraan' => 'Mobil',
                    'tarif_id' => $tarifMobil->id,
                    'area_parkir_id' => $areaDepan->id,
                    'waktu_masuk' => $pastDate->copy()->startOfDay()->addHours(14),
                    'waktu_keluar' => $pastDate->copy()->startOfDay()->addHours(18),
                    'durasi_menit' => 240,
                    'total_bayar' => 20000,
                    'uang_diterima' => 50000,
                    'kembalian' => 30000,
                    'metode_bayar' => 'Tunai',
                    'status' => 'selesai',
                    'petugas_id' => $petugas1->id,
                ]
            );
        }

        // 5. Data Log Aktivitas Awal
        ActivityLog::firstOrCreate(
            ['action' => 'Inisialisasi Sistem', 'user_id' => $admin->id],
            [
                'description' => 'Sistem SmarPARK berhasil dikonfigurasi dan diinisialisasi oleh Admin Utama.',
                'created_at' => now()->subDays(2),
            ]
        );

        ActivityLog::firstOrCreate(
            ['action' => 'Tambah Area Parkir', 'user_id' => $admin->id],
            [
                'description' => 'Admin menambahkan area parkir baru: Lantai 1 - Depan (Kapasitas: 30 slot).',
                'created_at' => now()->subDay(),
            ]
        );

        ActivityLog::firstOrCreate(
            ['action' => 'Tambah Tarif', 'user_id' => $admin->id],
            [
                'description' => 'Admin menetapkan tarif baru untuk kendaraan jenis Mobil (Rp 5.000/jam).',
                'created_at' => now()->subHours(12),
            ]
        );

        ActivityLog::firstOrCreate(
            ['action' => 'Kendaraan Masuk', 'user_id' => $petugas1->id],
            [
                'description' => 'Petugas mencatat kendaraan masuk plat B 1928 XYZ (Mobil) di Lantai 1 - Depan.',
                'created_at' => now()->subHours(4),
            ]
        );

        ActivityLog::firstOrCreate(
            ['action' => 'Kendaraan Keluar', 'user_id' => $petugas1->id],
            [
                'description' => 'Petugas menyelesaikan transaksi parkir B 7721 ZAA. Total bayar: Rp 15.000.',
                'created_at' => now()->subHours(1),
            ]
        );

        // 6. Pengaturan Sistem Awal
        Setting::set('app_name', 'SmarPARK');
        Setting::set('company_name', 'PT Solusi Parkir Pintar');
        Setting::set('company_address', 'Jl. Jenderal Sudirman No. 88, Jakarta Pusat');
        Setting::set('company_phone', '0812-9876-5432');
        Setting::set('tolerance_minutes', 10);
        Setting::set('footer_text', 'Terima kasih atas kunjungan Anda. Harap simpan bukti pembayaran.');
        Setting::set('receipt_paper_size', '80mm', 'petugas');
    }
}
