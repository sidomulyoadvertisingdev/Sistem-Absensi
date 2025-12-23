<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * SCOPES (OPSIONAL TAPI BERGUNA)
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
     * HELPERS
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
}
