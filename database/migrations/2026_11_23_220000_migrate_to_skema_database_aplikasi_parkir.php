<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Sesuai dengan skema database dari gambar user:
     * - tb_user
     * - tb_tarif
     * - tb_kendaraan
     * - tb_area_parkir
     * - tb_transaksi
     * - tb_log_aktivitas
     */
    public function up(): void
    {
        // 1. tb_user
        if (!Schema::hasTable('tb_user')) {
            Schema::create('tb_user', function (Blueprint $table) {
                $table->integer('id_user', true); // int(11) AUTO_INCREMENT PRIMARY KEY
                $table->string('nama_lengkap', 50);
                $table->string('username', 50)->unique();
                $table->string('password', 100);
                $table->enum('role', ['admin', 'petugas', 'owner'])->default('petugas');
                $table->tinyInteger('status_aktif')->default(1);
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // 2. tb_tarif
        if (!Schema::hasTable('tb_tarif')) {
            Schema::create('tb_tarif', function (Blueprint $table) {
                $table->integer('id_tarif', true); // int(11) AUTO_INCREMENT PRIMARY KEY
                $table->enum('jenis_kendaraan', ['motor', 'mobil', 'lainnya'])->unique();
                $table->decimal('tarif_per_jam', 10, 0);
                $table->timestamps();
            });
        }

        // 3. tb_kendaraan
        if (!Schema::hasTable('tb_kendaraan')) {
            Schema::create('tb_kendaraan', function (Blueprint $table) {
                $table->integer('id_kendaraan', true); // int(11) AUTO_INCREMENT PRIMARY KEY
                $table->string('plat_nomor', 15)->index();
                $table->string('jenis_kendaraan', 20);
                $table->string('warna', 20)->nullable();
                $table->string('pemilik', 100)->nullable();
                $table->integer('id_user');
                $table->timestamps();

                $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
            });
        }

        // 4. tb_area_parkir
        if (!Schema::hasTable('tb_area_parkir')) {
            Schema::create('tb_area_parkir', function (Blueprint $table) {
                $table->integer('id_area', true); // int(11) AUTO_INCREMENT PRIMARY KEY
                $table->string('nama_area', 50);
                $table->integer('kapasitas')->default(0);
                $table->integer('terisi')->default(0);
                $table->timestamps();
            });
        }

        // 5. tb_transaksi
        if (!Schema::hasTable('tb_transaksi')) {
            Schema::create('tb_transaksi', function (Blueprint $table) {
                $table->integer('id_parkir', true); // int(11) AUTO_INCREMENT PRIMARY KEY
                $table->integer('id_kendaraan');
                $table->dateTime('waktu_masuk');
                $table->dateTime('waktu_keluar')->nullable();
                $table->integer('id_tarif');
                $table->integer('durasi_jam')->nullable();
                $table->decimal('biaya_total', 10, 0)->nullable();
                $table->enum('status', ['masuk', 'keluar'])->default('masuk');
                $table->integer('id_user');
                $table->integer('id_area');
                $table->timestamps();

                $table->foreign('id_kendaraan')->references('id_kendaraan')->on('tb_kendaraan')->onDelete('cascade');
                $table->foreign('id_tarif')->references('id_tarif')->on('tb_tarif')->onDelete('restrict');
                $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
                $table->foreign('id_area')->references('id_area')->on('tb_area_parkir')->onDelete('restrict');
            });
        }

        // 6. tb_log_aktivitas
        if (!Schema::hasTable('tb_log_aktivitas')) {
            Schema::create('tb_log_aktivitas', function (Blueprint $table) {
                $table->integer('id_log', true); // int(11) AUTO_INCREMENT PRIMARY KEY
                $table->integer('id_user');
                $table->string('aktivitas', 100);
                $table->dateTime('waktu_aktivitas');

                $table->foreign('id_user')->references('id_user')->on('tb_user')->onDelete('cascade');
            });
        }

        // =========================================================
        // DATA MIGRATION: Pindahkan data lama ke tabel skema baru jika ada
        // =========================================================
        
        // A. Pindahkan users -> tb_user
        if (Schema::hasTable('users') && DB::table('tb_user')->count() === 0) {
            $oldUsers = DB::table('users')->get();
            foreach ($oldUsers as $u) {
                $username = !empty($u->email) ? explode('@', $u->email)[0] : 'user' . $u->id;
                // Pastikan username unik
                $baseUsername = substr($username, 0, 45);
                $finalUsername = $baseUsername;
                $counter = 1;
                while (DB::table('tb_user')->where('username', $finalUsername)->exists()) {
                    $finalUsername = $baseUsername . $counter++;
                }

                DB::table('tb_user')->insert([
                    'id_user' => $u->id,
                    'nama_lengkap' => substr($u->name ?? 'User ' . $u->id, 0, 50),
                    'username' => $finalUsername,
                    'password' => $u->password,
                    'role' => in_array($u->role ?? '', ['admin', 'petugas', 'owner']) ? $u->role : 'petugas',
                    'status_aktif' => isset($u->is_active) ? ($u->is_active ? 1 : 0) : 1,
                    'remember_token' => $u->remember_token ?? null,
                    'created_at' => $u->created_at ?? now(),
                    'updated_at' => $u->updated_at ?? now(),
                ]);
            }
        }

        // B. Pindahkan tarifs -> tb_tarif
        if (Schema::hasTable('tarifs') && DB::table('tb_tarif')->count() === 0) {
            $oldTarifs = DB::table('tarifs')->get();
            foreach ($oldTarifs as $t) {
                $jenisLower = strtolower($t->jenis_kendaraan);
                if (str_contains($jenisLower, 'motor')) {
                    $jenis = 'motor';
                } elseif (str_contains($jenisLower, 'mobil')) {
                    $jenis = 'mobil';
                } else {
                    $jenis = 'lainnya';
                }

                if (!DB::table('tb_tarif')->where('jenis_kendaraan', $jenis)->exists()) {
                    DB::table('tb_tarif')->insert([
                        'id_tarif' => $t->id,
                        'jenis_kendaraan' => $jenis,
                        'tarif_per_jam' => round($t->tarif_per_jam),
                        'created_at' => $t->created_at ?? now(),
                        'updated_at' => $t->updated_at ?? now(),
                    ]);
                }
            }

            // Default tarif jika kosong
            $defaultTarifs = [
                ['jenis_kendaraan' => 'motor', 'tarif_per_jam' => 2000],
                ['jenis_kendaraan' => 'mobil', 'tarif_per_jam' => 5000],
                ['jenis_kendaraan' => 'lainnya', 'tarif_per_jam' => 10000],
            ];
            foreach ($defaultTarifs as $dt) {
                if (!DB::table('tb_tarif')->where('jenis_kendaraan', $dt['jenis_kendaraan'])->exists()) {
                    DB::table('tb_tarif')->insert([
                        'jenis_kendaraan' => $dt['jenis_kendaraan'],
                        'tarif_per_jam' => $dt['tarif_per_jam'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // C. Pindahkan area_parkirs -> tb_area_parkir
        if (Schema::hasTable('area_parkirs') && DB::table('tb_area_parkir')->count() === 0) {
            $oldAreas = DB::table('area_parkirs')->get();
            foreach ($oldAreas as $a) {
                $terisi = 0;
                if (Schema::hasTable('transaksis')) {
                    $terisi = DB::table('transaksis')
                        ->where('area_parkir_id', $a->id)
                        ->where('status', 'parkir')
                        ->count();
                }

                DB::table('tb_area_parkir')->insert([
                    'id_area' => $a->id,
                    'nama_area' => substr($a->nama_area, 0, 50),
                    'kapasitas' => $a->kapasitas_total ?? 20,
                    'terisi' => $terisi,
                    'created_at' => $a->created_at ?? now(),
                    'updated_at' => $a->updated_at ?? now(),
                ]);
            }
        }

        // D. Pindahkan transaksis -> tb_kendaraan & tb_transaksi
        if (Schema::hasTable('transaksis') && DB::table('tb_transaksi')->count() === 0) {
            $oldTx = DB::table('transaksis')->get();
            $defaultUserId = DB::table('tb_user')->value('id_user') ?? 1;
            $defaultTarifId = DB::table('tb_tarif')->value('id_tarif') ?? 1;
            $defaultAreaId = DB::table('tb_area_parkir')->value('id_area') ?? 1;

            foreach ($oldTx as $tx) {
                // Cari atau buat kendaraan
                $plat = substr(strtoupper(trim($tx->plat_nomor ?? 'B 1234 XX')), 0, 15);
                $jenisKendaraan = substr($tx->jenis_kendaraan ?? 'mobil', 0, 20);
                $userId = DB::table('tb_user')->where('id_user', $tx->petugas_id)->exists() ? $tx->petugas_id : $defaultUserId;

                $kendaraan = DB::table('tb_kendaraan')->where('plat_nomor', $plat)->first();
                if (!$kendaraan) {
                    $idKendaraan = DB::table('tb_kendaraan')->insertGetId([
                        'plat_nomor' => $plat,
                        'jenis_kendaraan' => $jenisKendaraan,
                        'warna' => null,
                        'pemilik' => null,
                        'id_user' => $userId,
                        'created_at' => $tx->created_at ?? now(),
                        'updated_at' => $tx->updated_at ?? now(),
                    ]);
                } else {
                    $idKendaraan = $kendaraan->id_kendaraan;
                }

                $tarifId = DB::table('tb_tarif')->where('id_tarif', $tx->tarif_id)->exists() ? $tx->tarif_id : $defaultTarifId;
                $areaId = DB::table('tb_area_parkir')->where('id_area', $tx->area_parkir_id)->exists() ? $tx->area_parkir_id : $defaultAreaId;
                $status = ($tx->status === 'parkir') ? 'masuk' : 'keluar';
                $durasiJam = $tx->durasi_menit ? ceil($tx->durasi_menit / 60) : null;
                $biayaTotal = $tx->total_bayar ? round($tx->total_bayar) : null;

                DB::table('tb_transaksi')->insert([
                    'id_parkir' => $tx->id,
                    'id_kendaraan' => $idKendaraan,
                    'waktu_masuk' => $tx->waktu_masuk,
                    'waktu_keluar' => $tx->waktu_keluar,
                    'id_tarif' => $tarifId,
                    'durasi_jam' => $durasiJam,
                    'biaya_total' => $biayaTotal,
                    'status' => $status,
                    'id_user' => $userId,
                    'id_area' => $areaId,
                    'created_at' => $tx->created_at ?? now(),
                    'updated_at' => $tx->updated_at ?? now(),
                ]);
            }
        }

        // E. Pindahkan activity_logs -> tb_log_aktivitas
        if (Schema::hasTable('activity_logs') && DB::table('tb_log_aktivitas')->count() === 0) {
            $defaultUserId = DB::table('tb_user')->value('id_user') ?? 1;
            $oldLogs = DB::table('activity_logs')->get();
            foreach ($oldLogs as $log) {
                $userId = DB::table('tb_user')->where('id_user', $log->user_id)->exists() ? $log->user_id : $defaultUserId;
                $aktivitasText = substr(($log->action ?? 'Aktivitas') . ': ' . ($log->description ?? ''), 0, 100);

                DB::table('tb_log_aktivitas')->insert([
                    'id_log' => $log->id,
                    'id_user' => $userId,
                    'aktivitas' => $aktivitasText,
                    'waktu_aktivitas' => $log->created_at ?? now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_log_aktivitas');
        Schema::dropIfExists('tb_transaksi');
        Schema::dropIfExists('tb_area_parkir');
        Schema::dropIfExists('tb_kendaraan');
        Schema::dropIfExists('tb_tarif');
        Schema::dropIfExists('tb_user');
    }
};
