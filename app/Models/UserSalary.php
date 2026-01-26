<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSalary extends Model
{
    protected $table = 'user_salaries';

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'user_id',
        'gaji_pokok',
        'tunjangan_umum',
        'tunjangan_transport',
        'tunjangan_thr',
        'tunjangan_kesehatan',
        'lembur_per_jam',
        'aktif',
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'aktif' => 'boolean',
    ];

    /**
     * ===============================
     * RELATION
     * ===============================
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ===============================
     * HELPER METHOD
     * ===============================
     */

    /**
     * Hitung gaji per hari (GAJI POKOK SAJA)
     */
    public function gajiPerHari(int $hariKerja = 22): float
    {
        if ($hariKerja <= 0) {
            return 0;
        }

        return ($this->gaji_pokok ?? 0) / $hariKerja;
    }

    /**
     * Hitung total tunjangan tetap
     */
    public function totalTunjangan(): int
    {
        return
            ($this->tunjangan_umum ?? 0) +
            ($this->tunjangan_transport ?? 0) +
            ($this->tunjangan_thr ?? 0) +
            ($this->tunjangan_kesehatan ?? 0);
    }

    /**
     * Hitung total gaji (TANPA ABSENSI & LEMBUR)
     * ⚠️ Jangan dipakai untuk payroll final
     */
    public function totalGajiMaster(): int
    {
        return
            ($this->gaji_pokok ?? 0) +
            $this->totalTunjangan();
    }
}
