<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /*
    |--------------------------------------------------------------------------
    | ROLE CONSTANTS
    |--------------------------------------------------------------------------
    */
    public const ROLE_OWNER       = 'owner';
    public const ROLE_ADMIN       = 'admin'; // legacy admin
    public const ROLE_ADMIN_STAFF = 'admin_staff';
    public const ROLE_HRD         = 'hrd';
    public const ROLE_KEUANGAN    = 'keuangan';
    public const ROLE_KARYAWAN    = 'karyawan';
    public const ROLE_USER        = 'user';

    public const ADMIN_PERMISSION_LABELS = [
        'dashboard'          => 'Dashboard',
        'users'              => 'Manajemen User',
        'karyawan'           => 'Data Karyawan',
        'absensi'            => 'Absensi',
        'lembur'             => 'Lembur',
        'jadwal'             => 'Jadwal Kerja',
        'gaji'               => 'Gaji & Payroll',
        'laporan'            => 'Laporan',
        'potongan'           => 'Aturan Potongan Gaji',
        'jobs'               => 'Lowongan & Pelamar',
        'job_todos'          => 'Job Todo',
        'pelanggaran'        => 'Pelanggaran',
        'submission_types'   => 'Jenis Pengajuan',
        'submission'         => 'Pengajuan Masuk',
        'announcements'      => 'Pengumuman',
    ];

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
        'profile_photo',

        // Mobile / App
        'app_version_seen',
        'admin_permissions',

        // Schedule
        'schedule_mode',
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
        'admin_permissions' => 'array',
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
     * Jadwal kerja per tanggal (fleksibel)
     */
    public function workScheduleDates(): HasMany
    {
        return $this->hasMany(WorkScheduleDate::class, 'user_id');
    }

    /**
     * Resolve jadwal kerja berdasarkan mode dan tanggal.
     */
    public function resolveWorkSchedule(string|Carbon $tanggal)
    {
        $date = $tanggal instanceof Carbon
            ? $tanggal->toDateString()
            : Carbon::parse($tanggal)->toDateString();

        $mode = $this->schedule_mode ?? 'per_hari';

        if ($mode === 'per_tanggal') {
            return WorkScheduleDate::where('user_id', $this->id)
                ->where('tanggal', $date)
                ->where('aktif', true)
                ->first();
        }

        $hari = strtolower(
            Carbon::parse($date)
                ->locale('id')
                ->isoFormat('dddd')
        );

        return WorkSchedule::where('user_id', $this->id)
            ->where('hari', $hari)
            ->where('aktif', true)
            ->first();
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

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isAdmin(): bool
    {
        // Tetap dipakai luas di code lama -> jadikan alias panel admin.
        return $this->isPanelAdmin();
    }

    public function isAdminStaff(): bool
    {
        return $this->role === self::ROLE_ADMIN_STAFF;
    }

    public function isHrd(): bool
    {
        return $this->role === self::ROLE_HRD;
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

    public function isPanelAdmin(): bool
    {
        return in_array($this->role, self::adminRoles(), true);
    }

    public static function adminRoles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_ADMIN,
            self::ROLE_ADMIN_STAFF,
            self::ROLE_HRD,
            self::ROLE_KEUANGAN,
        ];
    }

    public static function adminRoleOptions(): array
    {
        return [
            self::ROLE_OWNER => 'Owner (Super Admin)',
            self::ROLE_HRD => 'HRD',
            self::ROLE_KEUANGAN => 'Keuangan',
            self::ROLE_ADMIN_STAFF => 'Admin Staff',
            self::ROLE_ADMIN => 'Admin (Legacy)',
        ];
    }

    public static function adminPermissionOptions(): array
    {
        return self::ADMIN_PERMISSION_LABELS;
    }

    public static function defaultPermissionsByRole(string $role): array
    {
        $all = array_keys(self::ADMIN_PERMISSION_LABELS);

        return match ($role) {
            self::ROLE_OWNER => $all,

            self::ROLE_HRD => [
                'dashboard',
                'users',
                'karyawan',
                'absensi',
                'lembur',
                'jadwal',
                'jobs',
                'job_todos',
                'pelanggaran',
                'submission',
                'announcements',
            ],

            self::ROLE_KEUANGAN => [
                'dashboard',
                'gaji',
                'laporan',
                'potongan',
                'submission',
                'lembur',
                'announcements',
            ],

            self::ROLE_ADMIN_STAFF => [
                'dashboard',
                'users',
                'karyawan',
                'absensi',
                'lembur',
                'jadwal',
                'submission',
                'announcements',
            ],

            // admin lama tetap luas agar tidak memutus alur lama.
            self::ROLE_ADMIN => array_values(array_diff($all, ['manage_admin_access'])),

            default => [],
        };
    }

    public function resolvedAdminPermissions(): array
    {
        if (!$this->isPanelAdmin()) {
            return [];
        }

        if ($this->isOwner()) {
            return array_keys(self::ADMIN_PERMISSION_LABELS);
        }

        $stored = is_array($this->admin_permissions)
            ? $this->admin_permissions
            : [];

        if (!empty($stored)) {
            return array_values(array_intersect(
                $stored,
                array_keys(self::ADMIN_PERMISSION_LABELS)
            ));
        }

        return self::defaultPermissionsByRole($this->role);
    }

    public function hasAdminPermission(string $permission): bool
    {
        if (!$this->isPanelAdmin()) {
            return false;
        }

        if ($permission === 'manage_admin_access') {
            return $this->isOwner();
        }

        if ($this->isOwner()) {
            return true;
        }

        return in_array($permission, $this->resolvedAdminPermissions(), true);
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
