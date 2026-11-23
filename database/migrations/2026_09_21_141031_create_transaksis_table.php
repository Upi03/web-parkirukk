<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_tiket')->unique();
            $table->string('plat_nomor');
            $table->string('jenis_kendaraan');
            $table->foreignId('tarif_id')->constrained('tarifs');
            $table->dateTime('waktu_masuk');
            $table->dateTime('waktu_keluar')->nullable();
            $table->integer('durasi_menit')->nullable();
            $table->decimal('total_bayar', 12, 2)->nullable();
            $table->decimal('uang_diterima', 12, 2)->nullable();
            $table->decimal('kembalian', 12, 2)->nullable();
            $table->string('metode_bayar')->nullable();
            $table->enum('status', ['parkir', 'selesai'])->default('parkir');
            $table->foreignId('petugas_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
