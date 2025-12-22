<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * ===============================
     * FILLABLE
     * ===============================
     * SEMUA FIELD WAJIB ADA DI SINI
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',

        // DATA KARYAWAN
        'nik',
        'phone',
        'address',
        'jabatan',
        'penempatan',
    ];

    /**
     * ===============================
     * HIDDEN
     * ===============================
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * ===============================
     * CASTS
     * ===============================
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * ===============================
     * RELATIONSHIPS
     * ===============================
     */

    // 🔗 USER → ABSENSI
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'user_id');
    }

    // 🔗 USER → JADWAL KERJA
    public function workSchedule(): HasOne
    {
        return $this->hasOne(WorkSchedule::class, 'user_id');
    }

    // 🔗 USER → GAJI
    public function salary(): HasOne
    {
        return $this->hasOne(UserSalary::class, 'user_id');
    }

    /**
     * ===============================
     * HELPER
     * ===============================
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
