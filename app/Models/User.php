<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted()
    {
        static::saved(function ($user) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('tb_user')) {
                    $username = !empty($user->email) ? explode('@', $user->email)[0] : 'user' . $user->id;
                    \Illuminate\Support\Facades\DB::table('tb_user')->updateOrInsert(
                        ['id_user' => $user->id],
                        [
                            'nama_lengkap' => substr($user->name ?? 'User ' . $user->id, 0, 50),
                            'username' => substr($username, 0, 50),
                            'password' => $user->password,
                            'role' => in_array($user->role ?? '', ['admin', 'petugas', 'owner']) ? $user->role : 'petugas',
                            'status_aktif' => isset($user->is_active) ? ($user->is_active ? 1 : 0) : 1,
                            'remember_token' => $user->remember_token ?? null,
                            'created_at' => $user->created_at ?? now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            } catch (\Throwable $e) {}
        });

        static::deleted(function ($user) {
            try {
                \Illuminate\Support\Facades\DB::table('tb_user')->where('id_user', $user->id)->delete();
            } catch (\Throwable $e) {}
        });
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function transaksis()
    {
        return $this->hasMany(Transaksi::class, 'petugas_id');
    }
}
