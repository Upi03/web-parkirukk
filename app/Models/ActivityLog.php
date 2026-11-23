<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActivityLog extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Catat log aktivitas baru (disimpan ke activity_logs dan otomatis disinkronkan ke tb_log_aktivitas)
     */
    public static function record(string $action, ?string $description = null, ?int $userId = null): self
    {
        $uid = $userId ?? auth()->id() ?? 1;

        $log = static::create([
            'user_id' => $uid,
            'action' => $action,
            'description' => $description,
        ]);

        // Sinkronisasi otomatis ke tb_log_aktivitas sesuai skema diagram
        try {
            if (Schema::hasTable('tb_log_aktivitas')) {
                $aktivitasText = substr($description ? "{$action}: {$description}" : $action, 0, 100);
                DB::table('tb_log_aktivitas')->insert([
                    'id_user' => $uid,
                    'aktivitas' => $aktivitasText,
                    'waktu_aktivitas' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Abaikan jika ada error sinkronisasi
        }

        return $log;
    }

    /**
     * Dapatkan warna badge untuk jenis aksi
     */
    public function getActionBadgeClassAttribute(): string
    {
        $action = strtolower($this->action);
        if (str_contains($action, 'tambah') || str_contains($action, 'masuk') || str_contains($action, 'create')) {
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400';
        }
        if (str_contains($action, 'edit') || str_contains($action, 'update') || str_contains($action, 'ubah')) {
            return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400';
        }
        if (str_contains($action, 'hapus') || str_contains($action, 'delete')) {
            return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
        }
        if (str_contains($action, 'keluar') || str_contains($action, 'checkout') || str_contains($action, 'selesai')) {
            return 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
        }
        if (str_contains($action, 'login') || str_contains($action, 'logout')) {
            return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400';
        }
        return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
    }
}
