<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Payroll extends Model
{
    use HasFactory;

    /**
     * ===============================
     * TABLE NAME
     * ===============================
     */
    protected $table = 'payrolls';

    /**
     * ===============================
     * MASS ASSIGNMENT
     * ===============================
     */
    protected $fillable = [
        'user_id',
        'periode',        // format: YYYY-MM (contoh: 2026-01)
        'total_gaji',
        'dibayar_pada',
    ];

    /**
     * ===============================
     * CASTING
     * ===============================
     */
    protected $casts = [
        'total_gaji'   => 'decimal:2',
        'dibayar_pada' => 'datetime',
    ];

    /**
     * ===============================
     * RELATION
     * ===============================
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ===============================
     * HELPER: CEK SUDAH DIBAYAR
     * ===============================
     */
    public function isPaid(): bool
    {
        return !is_null($this->dibayar_pada);
    }

    /**
     * ===============================
     * HELPER: FORMAT PERIODE
     * ===============================
     */
    public function getPeriodeLabelAttribute(): string
    {
        return Carbon::createFromFormat('Y-m', $this->periode)
            ->translatedFormat('F Y');
    }

    /**
     * ===============================
     * SCOPE: PERIODE
     * ===============================
     */
    public function scopePeriode($query, string $periode)
    {
        return $query->where('periode', $periode);
    }

    /**
     * ===============================
     * SCOPE: SUDAH DIBAYAR
     * ===============================
     */
    public function scopePaid($query)
    {
        return $query->whereNotNull('dibayar_pada');
    }

    /**
     * ===============================
     * SCOPE: BELUM DIBAYAR
     * ===============================
     */
    public function scopeUnpaid($query)
    {
        return $query->whereNull('dibayar_pada');
    }
}
