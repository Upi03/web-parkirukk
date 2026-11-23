<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah jenis_kendaraan di area_parkirs jika belum ada
        Schema::table('area_parkirs', function (Blueprint $table) {
            if (!Schema::hasColumn('area_parkirs', 'jenis_kendaraan')) {
                $table->string('jenis_kendaraan', 50)->nullable()->after('nama_area');
            }
        });

        // 2. Tambah kolom pembatalan dan snapshot tarif di transaksis
        Schema::table('transaksis', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksis', 'alasan_batal')) {
                $table->text('alasan_batal')->nullable()->after('status');
            }
            if (!Schema::hasColumn('transaksis', 'tarif_snapshot')) {
                $table->json('tarif_snapshot')->nullable()->after('tarif_id');
            }
        });

        // 3. Perluas enum/tipe status pada transaksis untuk mendukung 'batal'
        try {
            DB::statement("ALTER TABLE transaksis MODIFY COLUMN status VARCHAR(20) NOT NULL DEFAULT 'parkir'");
        } catch (\Throwable $e) {
            // fallback jika database non-mysql
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksis', function (Blueprint $table) {
            if (Schema::hasColumn('transaksis', 'alasan_batal')) {
                $table->dropColumn('alasan_batal');
            }
            if (Schema::hasColumn('transaksis', 'tarif_snapshot')) {
                $table->dropColumn('tarif_snapshot');
            }
        });

        Schema::table('area_parkirs', function (Blueprint $table) {
            if (Schema::hasColumn('area_parkirs', 'jenis_kendaraan')) {
                $table->dropColumn('jenis_kendaraan');
            }
        });
    }
};
