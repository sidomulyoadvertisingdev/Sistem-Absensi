<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkScheduleDate extends Model
{
    /**
     * ===============================
     * TABLE
     * ===============================
     */
    protected $table = 'work_schedule_dates';

    /**
     * ===============================
     * FILLABLE
     * ===============================
     */
    protected $fillable = [
        'user_id',
        'tanggal',
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
        'tanggal' => 'date',
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
     * HELPERS
     * ===============================
     */
    public function isActive(): bool
    {
        return (bool) $this->aktif;
    }

    public function hasWorkingHours(): bool
    {
        return !empty($this->jam_masuk)
            && !empty($this->jam_pulang);
    }
}
