<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WorkSchedule extends Model
{
    /**
     * ===============================
     * TABLE
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
        'hari',
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
     * NORMALISASI HARI
     * ===============================
     * 🔥 Mencegah bug edit jadwal
     */
    public function setHariAttribute($value)
    {
        $this->attributes['hari'] = strtolower(trim($value));
    }

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

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeLibur($query)
    {
        return $query->where('aktif', false);
    }

    /**
     * ===============================
     * HELPERS
     * ===============================
     */

    public function isActive(): bool
    {
        return (bool) $this->aktif;
    }

    public function isHoliday(): bool
    {
        return !$this->aktif;
    }

    public function hariLabel(): string
    {
        return ucfirst($this->hari);
    }

    /**
     * ===============================
     * AMBIL JADWAL HARI INI
     * ===============================
     */
    public static function jadwalHariIni(int $userId): ?self
    {
        // hasil: senin, selasa, dst
        $hari = strtolower(
            Carbon::now()
                ->locale('id')
                ->isoFormat('dddd')
        );

        return self::where('user_id', $userId)
            ->where('hari', $hari)
            ->where('aktif', true)
            ->first();
    }

    /**
     * ===============================
     * BATAS TERLAMBAT
     * ===============================
     */
    public function batasTerlambat(int $menit = 15): ?Carbon
    {
        if (!$this->jam_masuk) {
            return null;
        }

        return Carbon::parse($this->jam_masuk)
            ->addMinutes($menit);
    }

    /**
     * ===============================
     * HELPER TAMBAHAN (AMAN)
     * ===============================
     * Untuk cek apakah jadwal valid
     */
    public function hasWorkingHours(): bool
    {
        return !empty($this->jam_masuk)
            && !empty($this->jam_pulang);
    }
}
