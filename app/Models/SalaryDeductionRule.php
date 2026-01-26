<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryDeductionRule extends Model
{
    protected $table = 'salary_deduction_rules';

    /**
     * Kolom yang boleh diisi (HARUS SAMA DENGAN DB)
     */
    protected $fillable = [
        // IDENTITAS
        'kode',              // TELAT_3X, OFF_LEBIH_5, dll
        'nama',              // Nama aturan
        'penempatan',        // Lokasi / toko (nullable = global)
        'keterangan',        // Deskripsi aturan

        // SISTEM POTONGAN
        'type',              // fixed | percentage
        'value',             // nominal atau persen
        'base_amount',       // gaji_pokok | salary_kotor | total_gaji

        // KONDISI
        'condition_type',    // pelanggaran | off_day | terlambat
        'condition_value',   // batas trigger (misal 3x, 5 hari)

        // STATUS
        'aktif',
    ];

    /**
     * Casting tipe data
     */
    protected $casts = [
        'aktif' => 'boolean',
        'value' => 'float',
        'condition_value' => 'integer',
    ];

    /* =====================================================
     * HELPER METHODS
     * ===================================================== */

    /**
     * Apakah rule berbentuk persentase
     */
    public function isPercentage(): bool
    {
        return $this->type === 'percentage';
    }

    /**
     * Apakah rule berbentuk nominal
     */
    public function isFixed(): bool
    {
        return $this->type === 'fixed';
    }

    /**
     * Hitung nilai potongan berdasarkan basis
     *
     * @param float $baseValue (gaji pokok / salary kotor / total gaji)
     * @return float
     */
    public function calculateDeduction(float $baseValue): float
    {
        if (!$this->aktif) {
            return 0;
        }

        if ($this->isPercentage()) {
            return ($this->value / 100) * $baseValue;
        }

        return $this->value;
    }

    /**
     * Cek apakah aturan berlaku untuk penempatan tertentu
     * (null = global)
     */
    public function isApplicableForPenempatan(?string $penempatan): bool
    {
        if (empty($this->penempatan)) {
            return true; // global
        }

        return $this->penempatan === $penempatan;
    }
}
