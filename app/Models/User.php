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

    /**
     * ===============================
     * FILLABLE
     * ===============================
     * SEMUA FIELD USER & KARYAWAN
     */
    protected $fillable = [
        // AUTH
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

        // MOBILE APP
        'app_version_seen',
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

        // ✅ PENTING: auto hash password
        'password' => 'hashed',
    ];

    /**
     * ===============================
     * RELATIONSHIPS
     * ===============================
     */

    /**
     * 🔗 USER → ABSENSI
     * 1 user punya banyak absensi
     */
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'user_id');
    }

    /**
     * 🔗 USER → JADWAL KERJA
     * 1 user punya banyak jadwal (Senin–Minggu)
     */
    public function workSchedules(): HasMany
    {
        return $this->hasMany(WorkSchedule::class, 'user_id');
    }

    /**
     * 🔗 USER → GAJI
     * 1 user punya 1 data gaji aktif
     */
    public function salary(): HasOne
    {
        return $this->hasOne(UserSalary::class, 'user_id');
    }

    /**
     * 🔗 USER → PELANGGARAN
     * 1 user bisa punya banyak pelanggaran
     */
    public function pelanggarans(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'user_id');
    }

    /**
     * ===============================
     * HELPER METHODS
     * ===============================
     */

    /**
     * Cek apakah user admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user karyawan
     */
    public function isEmployee(): bool
    {
        return $this->role === 'user';
    }
}
