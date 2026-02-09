<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
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
        'gaji_harian_mode',
        'auto_generate_harian',

        // 🔥 RULE PAYROLL
        'include_tunjangan',
        'training_enabled',
        'training_start_date',
        'training_duration_days',
        'training_deduction_type',
        'training_deduction_value',

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
        'training_enabled'   => 'boolean',
        'auto_generate_harian' => 'boolean',

        'is_paid'            => 'boolean',
        'paid_at'            => 'datetime',
        'training_start_date' => 'date',
        'gaji_harian_mode'   => 'string',

        'gaji_pokok'         => 'float',
        'gaji_harian'        => 'float',

        'tunjangan_umum'     => 'float',
        'tunjangan_transport'=> 'float',
        'tunjangan_thr'      => 'float',
        'tunjangan_kesehatan'=> 'float',

        'lembur_per_jam'     => 'float',
        'training_deduction_value' => 'float',
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

        $basis = (float) ($this->gaji_pokok ?? 0);

        if (($this->gaji_harian_mode ?? null) === 'pokok_plus_tunjangan') {
            $basis += $this->totalTunjangan();
        }

        return $basis / $hariKerja;
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

    /**
     * Cek periode training terhadap bulan payroll.
     */
    public function getTrainingWindowForPeriod(CarbonInterface $periodDate): array
    {
        $periodStart = Carbon::parse($periodDate)->startOfMonth()->startOfDay();
        $periodEnd = Carbon::parse($periodDate)->endOfMonth()->endOfDay();
        $daysInPeriod = (int) $periodStart->daysInMonth;

        $default = [
            'active' => false,
            'start' => null,
            'end' => null,
            'overlap_days' => 0,
            'days_in_period' => $daysInPeriod,
            'ratio' => 0.0,
        ];

        if (
            !$this->training_enabled
            || empty($this->training_start_date)
            || (int) $this->training_duration_days <= 0
        ) {
            return $default;
        }

        $trainingStart = Carbon::parse($this->training_start_date)->startOfDay();
        $trainingEnd = $trainingStart->copy()
            ->addDays(((int) $this->training_duration_days) - 1)
            ->endOfDay();

        $overlapStart = $trainingStart->greaterThan($periodStart)
            ? $trainingStart->copy()
            : $periodStart->copy();

        $overlapEnd = $trainingEnd->lessThan($periodEnd)
            ? $trainingEnd->copy()
            : $periodEnd->copy();

        $active = $overlapStart->lessThanOrEqualTo($overlapEnd);
        $overlapDays = $active
            ? ($overlapStart->diffInDays($overlapEnd) + 1)
            : 0;

        return [
            'active' => $active,
            'start' => $trainingStart,
            'end' => $trainingEnd,
            'overlap_days' => $overlapDays,
            'days_in_period' => $daysInPeriod,
            'ratio' => $daysInPeriod > 0
                ? round($overlapDays / $daysInPeriod, 6)
                : 0.0,
        ];
    }

    /**
     * Hitung nominal potongan training untuk periode payroll.
     */
    public function calculateTrainingDeduction(
        float $salaryKotor,
        CarbonInterface $periodDate
    ): array {
        $training = $this->getTrainingWindowForPeriod($periodDate);

        if (!$training['active']) {
            return array_merge($training, [
                'deduction_type' => $this->training_deduction_type ?: 'percentage',
                'deduction_value' => (float) ($this->training_deduction_value ?? 0),
                'daily_salary_used' => max((float) $this->getGajiHarian(), 0),
                'deduction_per_day' => 0.0,
                'deduction_nominal' => 0.0,
            ]);
        }

        $type = $this->training_deduction_type === 'fixed'
            ? 'fixed'
            : 'percentage';

        $value = max((float) ($this->training_deduction_value ?? 0), 0);
        $overlapDays = (int) ($training['overlap_days'] ?? 0);
        $dailySalary = max((float) $this->getGajiHarian(), 0);

        $deductionPerDay = 0.0;

        if ($type === 'percentage') {
            $deductionPerDay = $dailySalary * ($value / 100);
        } else {
            $deductionPerDay = $value;
        }

        $nominal = $deductionPerDay * $overlapDays;
        $nominal = min(max($nominal, 0), max($salaryKotor, 0));

        return array_merge($training, [
            'deduction_type' => $type,
            'deduction_value' => $value,
            'daily_salary_used' => round($dailySalary, 2),
            'deduction_per_day' => round(max($deductionPerDay, 0), 2),
            'deduction_nominal' => round(max($nominal, 0), 2),
        ]);
    }
}

