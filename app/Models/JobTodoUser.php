<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobTodoUser extends Pivot
{
    protected $table = 'job_todo_user';

    protected $fillable = [
        'job_todo_id',
        'user_id',
        'status',        // pending | accepted | completed
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * ======================================================
     * RELASI KE JOB TODO
     * ======================================================
     */
    public function jobTodo(): BelongsTo
    {
        return $this->belongsTo(JobTodo::class);
    }

    /**
     * ======================================================
     * RELASI KE USER
     * ======================================================
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ======================================================
     * HELPER STATUS BADGE (UNTUK UI)
     * ======================================================
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'secondary',
            'accepted'  => 'warning',
            'completed' => 'success',
            default     => 'light',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Menunggu',
            'accepted'  => 'Dikerjakan',
            'completed' => 'Selesai',
            default     => ucfirst($this->status),
        };
    }
}
