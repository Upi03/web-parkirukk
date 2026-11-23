<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'tb_log_aktivitas';
    protected $primaryKey = 'id_log';
    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'aktivitas',
        'waktu_aktivitas',
    ];

    protected $casts = [
        'waktu_aktivitas' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    /**
     * Catat log aktivitas baru ke tb_log_aktivitas
     */
    public static function record(string $aktivitas, ?int $userId = null): self
    {
        return static::create([
            'id_user' => $userId ?? auth()->id() ?? 1,
            'aktivitas' => substr($aktivitas, 0, 100),
            'waktu_aktivitas' => now(),
        ]);
    }
}

