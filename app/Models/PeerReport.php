<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeerReport extends Model
{
    public const CATEGORIES = [
        'Kedisiplinan',
        'Sikap Kerja',
        'Keamanan',
        'Kualitas Kerja',
        'Lainnya',
    ];

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'kategori',
        'lokasi',
        'kronologi',
        'bukti_path',
        'status',
        'reviewed_by',
        'reviewed_at',
        'catatan_admin',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
