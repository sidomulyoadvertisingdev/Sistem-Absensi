<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSalary extends Model
{
    protected $table = 'user_salaries';

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'user_id',

        // 🔥 GAJI
        'gaji_pokok',
        'gaji_harian',

        // 🔥 RULE PAYROLL
        'include_tunjangan',

        // Tunjangan
        'tunjangan_umum',
        'tunjangan_transport',
        'tunjangan_thr',
        'tunjangan_kesehatan',

        // Lembur
        'lembur_per_jam',

        // Status
        'aktif',

        // Payroll tracking
        'is_paid',
        'payroll_period',
        'paid_at',
        'paid_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTING — payroll safe
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'aktif'              => 'boolean',
        'include_tunjangan'  => 'boolean',

        'is_paid'            => 'boolean',
        'paid_at'            => 'datetime',

        'gaji_pokok'         => 'float',
        'gaji_harian'        => 'float',

        'tunjangan_umum'     => 'float',
        'tunjangan_transport'=> 'float',
        'tunjangan_thr'      => 'float',
        'tunjangan_kesehatan'=> 'float',

        'lembur_per_jam'     => 'float',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATION
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER — PAYROLL ENGINE
    |--------------------------------------------------------------------------
    */

    /**
     * 🔥 Ambil gaji harian final
     *
     * Prioritas:
     * 1️⃣ gaji_harian manual
     * 2️⃣ fallback dari gaji pokok
     */
    public function getGajiHarian(int $hariKerja = 26): float
    {
        if (!empty($this->gaji_harian)) {
            return $this->gaji_harian;
        }

        if ($hariKerja <= 0) {
            return 0;
        }

        return ($this->gaji_pokok ?? 0) / $hariKerja;
    }

    /**
     * Total tunjangan tetap
     */
    public function totalTunjangan(): float
    {
        return
            ($this->tunjangan_umum ?? 0) +
            ($this->tunjangan_transport ?? 0) +
            ($this->tunjangan_thr ?? 0) +
            ($this->tunjangan_kesehatan ?? 0);
    }

    /**
     * 🔥 Hitung tunjangan payroll
     * mengikuti checkbox include_tunjangan
     */
    public function tunjanganUntukPayroll(): float
    {
        return $this->include_tunjangan
            ? $this->totalTunjangan()
            : 0;
    }

    /**
     * Total gaji master (tanpa absensi)
     */
    public function totalGajiMaster(): float
    {
        return
            ($this->gaji_pokok ?? 0) +
            $this->totalTunjangan();
    }

    /**
     * Cek payroll bulan tertentu
     */
    public function isPaidFor(string $bulanYm): bool
    {
        return $this->is_paid === true
            && $this->payroll_period === $bulanYm;
    }
}
