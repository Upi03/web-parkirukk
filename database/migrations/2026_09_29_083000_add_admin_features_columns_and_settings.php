<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add is_active to users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
        });

        // 2. Add tarif_maksimal to tarifs
        Schema::table('tarifs', function (Blueprint $table) {
            if (!Schema::hasColumn('tarifs', 'tarif_maksimal')) {
                $table->decimal('tarif_maksimal', 10, 2)->nullable()->after('tarif_pertama');
            }
        });

        // 3. Add area_parkir_id to transaksis
        Schema::table('transaksis', function (Blueprint $table) {
            if (!Schema::hasColumn('transaksis', 'area_parkir_id')) {
                $table->foreignId('area_parkir_id')->nullable()->after('tarif_id')->constrained('area_parkirs')->nullOnDelete();
            }
        });

        // 4. Create settings table
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');

        Schema::table('transaksis', function (Blueprint $table) {
            if (Schema::hasColumn('transaksis', 'area_parkir_id')) {
                $table->dropForeign(['area_parkir_id']);
                $table->dropColumn('area_parkir_id');
            }
        });

        Schema::table('tarifs', function (Blueprint $table) {
            if (Schema::hasColumn('tarifs', 'tarif_maksimal')) {
                $table->dropColumn('tarif_maksimal');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
