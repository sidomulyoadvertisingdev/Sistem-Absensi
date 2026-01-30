<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryDeductionRule extends Model
{
    protected $table = 'salary_deduction_rules';

    protected $fillable = [
        'kode',
        'nama',
        'penempatan',
        'keterangan',
        'type',
        'value',
        'base_amount',
        'condition_type',
        'condition_value',
        'max_occurrence',
        'max_minutes',
        'aktif',
    ];

    protected $casts = [
        'aktif'           => 'boolean',
        'value'           => 'float',
        'condition_value' => 'integer',
        'max_occurrence'  => 'integer',
        'max_minutes'     => 'integer',
        'penempatan'      => 'array',
    ];

    /* ================= STATUS ================= */

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

    /* ================= PENEMPATAN ================= */

    public function isApplicableForPenempatan(?string $userPenempatan): bool
    {
        if (empty($this->penempatan)) {
            return true; // GLOBAL
        }

        if (empty($userPenempatan)) {
            return false;
        }

        return in_array($userPenempatan, $this->penempatan, true);
    }

    /* ================= HITUNG POTONGAN ================= */

    public function calculate(float $basis): float
    {
        if (!$this->isActive()) {
            return 0;
        }

        return $this->isPercentage()
            ? ($this->value / 100) * $basis
            : (float) $this->value;
    }

    public function calculateDeduction(float $basis): float
    {
        return $this->calculate($basis);
    }

    public function calculateBaseDeduction(float $basis): float
    {
        return $this->calculate($basis);
    }

    /* ================= BATASAN ================= */

    public function limitOccurrence(int $actual): int
    {
        return $this->max_occurrence && $this->max_occurrence > 0
            ? min($actual, $this->max_occurrence)
            : $actual;
    }

    public function limitMinutes(int $minutes): int
    {
        return $this->max_minutes && $this->max_minutes > 0
            ? min($minutes, $this->max_minutes)
            : $minutes;
    }
}
