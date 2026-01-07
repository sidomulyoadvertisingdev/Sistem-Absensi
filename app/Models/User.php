<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | ROLE CONSTANTS
    |--------------------------------------------------------------------------
    */
    public const ROLE_ADMIN    = 'admin';
    public const ROLE_KARYAWAN = 'karyawan';
    public const ROLE_KEUANGAN = 'keuangan';
    public const ROLE_USER     = 'user';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNABLE
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        // Auth
        'name',
        'email',
        'password',
        'role',

        // Profile / Karyawan
        'nik',
        'phone',
        'address',
        'jabatan',
        'penempatan',

        // Mobile / App
        'app_version_seen',
    ];

    /*
    |--------------------------------------------------------------------------
    | HIDDEN ATTRIBUTES
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel 10+
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * 🕒 ABSENSI (KHUSUS KARYAWAN)
     */
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'user_id');
    }

    /**
     * 📆 JADWAL KERJA
     */
    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class, 'user_id');
    }

    /**
     * 💰 GAJI (KHUSUS KARYAWAN)
     */
    public function salary(): HasOne
    {
        return $this->hasOne(UserSalary::class, 'user_id');
    }

    /**
     * ⚠️ PELANGGARAN
     */
    public function pelanggarans(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'user_id');
    }

    /**
     * 🧾 RIWAYAT LAMARAN PEKERJAAN (USER / PUBLIC)
     */
    public function jobApplicants(): HasMany
    {
        return $this->hasMany(JobApplicant::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE HELPERS (AUTHORIZATION)
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isKaryawan(): bool
    {
        return $this->role === self::ROLE_KARYAWAN;
    }

    public function isKeuangan(): bool
    {
        return $this->role === self::ROLE_KEUANGAN;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function jobTodos()
{
    return $this->belongsToMany(JobTodo::class)
        ->withPivot([
            'status',
            'completed_at',
        ])
        ->withTimestamps();
}

}
