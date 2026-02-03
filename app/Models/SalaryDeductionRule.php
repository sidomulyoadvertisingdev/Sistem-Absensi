<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryDeductionRule extends Model
{
    protected $table = 'salary_deduction_rules';

    /**
     * ===============================
     * MASS ASSIGNMENT
     * ===============================
     */
    protected $fillable = [
        'kode',
        'nama',
        'keterangan',

        // fixed | percentage
        'type',
        'value',

        // gaji_pokok | tunjangan | total_gaji
        'base_source',

        // array jenis tunjangan (diambil dari aturan gaji)
        'tunjangan_items',

        'condition_type',
        'condition_value',

        'max_occurrence',
        'max_minutes',

        // WAJIB & STRICT
        'penempatan',

        'aktif',
    ];

    /**
     * ===============================
     * CASTING
     * ===============================
     */
    protected $casts = [
        'aktif'           => 'boolean',
        'value'           => 'float',
        'penempatan'      => 'array',
        'tunjangan_items' => 'array',
    ];

    /**
     * ===============================
     * DEFAULT VALUE SAFETY
     * ===============================
     */
    protected $attributes = [
        'tunjangan_items' => '[]',
    ];

    /* ===============================
     * STATUS
     * =============================== */
    public function isActive(): bool
    {
        return $this->aktif === true;
    }

    public function isPercentage(): bool
    {
        return $this->type === 'percentage';
    }

    public function isFixed(): bool
    {
        return $this->type === 'fixed';
    }

    /* ===============================
     * PENEMPATAN (STRICT)
     * =============================== */
    public function isApplicableForPenempatan(?string $userPenempatan): bool
    {
        // aturan & karyawan WAJIB punya penempatan
        if (empty($this->penempatan) || empty($userPenempatan)) {
            return false;
        }

        return in_array($userPenempatan, $this->penempatan, true);
    }

    /* ===============================
     * BASE SOURCE CHECK
     * =============================== */
    public function isFromGajiPokok(): bool
    {
        return $this->base_source === 'gaji_pokok';
    }

    public function isFromTunjangan(): bool
    {
        return $this->base_source === 'tunjangan';
    }

    public function isFromTotalGaji(): bool
    {
        return $this->base_source === 'total_gaji';
    }

    /* ===============================
     * HITUNG NILAI POTONGAN
     * =============================== */
    public function calculate(float $basis): float
    {
        if (!$this->isActive()) {
            return 0;
        }

        return $this->isPercentage()
            ? round(($this->value / 100) * $basis, 2)
            : (float) $this->value;
    }

    /**
     * ===============================
     * HITUNG POTONGAN DARI TUNJANGAN
     * ===============================
     * $tunjangan = [
     *   'transport' => 500000,
     *   'makan' => 300000
     * ]
     */
    public function calculateFromTunjangan(array $tunjangan): float
    {
        if (!$this->isFromTunjangan()) {
            return 0;
        }

        $items = $this->tunjangan_items ?? [];

        if (empty($items)) {
            return 0;
        }

        $basis = 0;

        foreach ($items as $item) {
            if (isset($tunjangan[$item])) {
                $basis += (float) $tunjangan[$item];
            }
        }

        return $this->calculate($basis);
    }
}
