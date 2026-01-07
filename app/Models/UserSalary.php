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
        'uang_makan',
        'transport',
        'lembur_per_jam',
        'bonus',          // 🔥 BONUS JOB
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
     * HELPER METHOD (AMAN DIPAKAI)
     * ===============================
     */

    /**
     * Tambah bonus ke gaji (dipakai saat job selesai)
     */
    public function addBonus(int $amount): void
    {
        $this->bonus = ($this->bonus ?? 0) + $amount;
        $this->save();
    }

    /**
     * Hitung total gaji keseluruhan
     */
    public function totalGaji(): int
    {
        return
            ($this->gaji_pokok ?? 0) +
            ($this->uang_makan ?? 0) +
            ($this->transport ?? 0) +
            ($this->bonus ?? 0);
    }
}
