<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Lembur extends Model
{
    use HasFactory;

    protected $table = 'lemburs'; // pastikan sesuai tabel

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'foto_mulai',
        'foto_selesai',
        'keterangan',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    protected $appends = ['foto_mulai_url', 'foto_selesai_url', 'durasi_jam'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Admin yang menyetujui lembur ini untuk dihitung ke gaji.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Durasi lembur dalam jam. Null-safe: kembalikan 0 bila jam_mulai/jam_selesai
     * belum lengkap (mis. lembur yang masih berjalan, belum ditekan "Selesai").
     *
     * PENTING: jangan pakai Carbon::parse($jam) langsung untuk kolom time yang
     * bisa NULL — Carbon::parse(null) diam-diam mengembalikan waktu SEKARANG,
     * bukan error, sehingga hasil diffInMinutes() bisa jadi angka ngawur/negatif.
     */
    public function getDurasiJamAttribute(): float
    {
        if (empty($this->jam_mulai) || empty($this->jam_selesai)) {
            return 0.0;
        }

        return Carbon::parse($this->jam_mulai)
            ->diffInMinutes(Carbon::parse($this->jam_selesai)) / 60;
    }

    /**
     * Scope: lembur yang SUDAH DISETUJUI UNTUK DIHITUNG KE GAJI.
     *
     * Dua jalur yang dianggap approved untuk payroll:
     *  1) status = 'approved' — jalur input manual admin (all-in-one, admin
     *     sekaligus jadi approver saat submit form, backward-compatible
     *     dengan data lama sebelum kolom approved_at/approved_by ada).
     *  2) status = 'finished' DAN approved_at terisi — jalur mobile app:
     *     user ajukan & selesaikan lembur sendiri, admin approve terpisah
     *     kapan saja (sebelum/sesudah finished) supaya nominal masuk gaji.
     */
    public function scopeApprovedForPayroll(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('status', 'approved')
                ->orWhere(function (Builder $q2) {
                    $q2->where('status', 'finished')
                        ->whereNotNull('approved_at');
                });
        });
    }

    /**
     * Scope: lembur yang MASIH MENUNGGU APPROVAL admin -- nominalnya belum
     * masuk gaji (masih pending). Tidak termasuk lembur yang sudah masuk
     * scopeApprovedForPayroll() maupun yang sudah ditolak (rejected).
     */
    public function scopePendingPayroll(Builder $query): Builder
    {
        return $query->whereNull('approved_at')
            ->where('status', '!=', 'rejected')
            ->where('status', '!=', 'approved');
    }

    /**
     * URL publik foto bukti awal lembur.
     * Kolom `foto_mulai` menyimpan path relatif (mis. "lembur/xxxx.jpg") hasil
     * store('lembur', 'public'). Butuh `php artisan storage:link` sekali.
     */
    public function getFotoMulaiUrlAttribute(): ?string
    {
        return $this->resolveFotoUrl($this->foto_mulai);
    }

    /**
     * URL publik foto bukti selesai lembur.
     */
    public function getFotoSelesaiUrlAttribute(): ?string
    {
        return $this->resolveFotoUrl($this->foto_selesai);
    }

    private function resolveFotoUrl(?string $foto): ?string
    {
        if (!$foto) {
            return null;
        }

        if (Str::startsWith($foto, ['http://', 'https://'])) {
            return $foto;
        }

        if (Str::startsWith($foto, 'storage/')) {
            return asset($foto);
        }

        return asset('storage/' . ltrim($foto, '/'));
    }
}
