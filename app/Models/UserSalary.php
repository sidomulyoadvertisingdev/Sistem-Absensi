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

        // Gaji
        'gaji_pokok',
        'tunjangan_umum',
        'tunjangan_transport',
        'tunjangan_thr',
        'tunjangan_kesehatan',
        'lembur_per_jam',

        // Status
        'aktif',

        // 🔥 PAYROLL
        'is_paid',
        'payroll_period',
        'paid_at',
        'paid_by',
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'aktif'     => 'boolean',
        'is_paid'   => 'boolean',
        'paid_at'   => 'datetime',
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
     * Admin yang membayar gaji
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * ===============================
     * HELPER METHOD
     * ===============================
     */

    /**
     * Hitung gaji per hari (GAJI POKOK SAJA)
     */
    public function gajiPerHari(int $hariKerja = 26): float
    {
        if ($hariKerja <= 0) {
            return 0;
        }

        return ($this->gaji_pokok ?? 0) / $hariKerja;
    }

    /**
     * Total tunjangan tetap
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
     * Total gaji master (TANPA absensi & lembur)
     * ⚠️ BUKAN payroll final
     */
    public function totalGajiMaster(): int
    {
        return
            ($this->gaji_pokok ?? 0) +
            $this->totalTunjangan();
    }

    /**
     * Apakah gaji sudah dibayar untuk bulan tertentu
     */
    public function isPaidFor(string $bulanYm): bool
    {
        return $this->is_paid === true && $this->payroll_period === $bulanYm;
    }
}
