<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WorkSchedule extends Model
{
    /**
     * ===============================
     * TABLE NAME
     * ===============================
     */
    protected $table = 'work_schedules';

    /**
     * ===============================
     * FILLABLE
     * ===============================
     */
    protected $fillable = [
        'user_id',
        'hari',               // senin - minggu
        'jam_masuk',
        'jam_pulang',
        'istirahat_mulai',
        'istirahat_selesai',
        'aktif',
    ];

    /**
     * ===============================
     * CASTS
     * ===============================
     */
    protected $casts = [
        'aktif' => 'boolean',
    ];

    /**
     * ===============================
     * RELATIONSHIP
     * ===============================
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ===============================
     * SCOPES
     * ===============================
     */

    /**
     * Scope hanya hari kerja
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope hari libur
     */
    public function scopeLibur($query)
    {
        return $query->where('aktif', false);
    }

    /**
     * ===============================
     * HELPERS (AMAN, TIDAK MERUSAK)
     * ===============================
     */

    /**
     * Apakah hari ini hari kerja
     */
    public function isActive(): bool
    {
        return (bool) $this->aktif;
    }

    /**
     * Apakah hari libur
     */
    public function isHoliday(): bool
    {
        return !$this->aktif;
    }

    /**
     * Label hari (rapi)
     */
    public function hariLabel(): string
    {
        return ucfirst($this->hari);
    }

    /**
     * ======================================================
     * HELPER BARU (INTI UNTUK STATUS HADIR / TERLAMBAT)
     * ======================================================
     * TIDAK MENGUBAH SISTEM LAMA
     * HANYA MEMUDAHKAN ABSENSI
     */

    /**
     * Ambil jadwal aktif user untuk hari ini
     * Return null jika libur / tidak ada jadwal
     */
    public static function jadwalHariIni(int $userId): ?self
    {
        // Hari sekarang (senin - minggu)
        $hari = strtolower(Carbon::now()->locale('id')->isoFormat('dddd'));
        // contoh: senin, selasa, rabu, dst

        return self::where('user_id', $userId)
            ->where('hari', $hari)
            ->where('aktif', true)
            ->first();
    }

    /**
     * Ambil jam masuk + toleransi (default 15 menit)
     */
    public function batasTerlambat(int $menit = 15): Carbon
    {
        return Carbon::parse($this->jam_masuk)->addMinutes($menit);
    }
}
