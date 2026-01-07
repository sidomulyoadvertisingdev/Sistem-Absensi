<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class JobTodo extends Model
{
    protected $table = 'job_todos';

    protected $fillable = [
        'title',
        'description',
        'bonus',
        'broadcast',
        'status', // open | closed
    ];

    protected $casts = [
        'broadcast' => 'boolean',
        'bonus'     => 'integer',
    ];

    /**
     * ======================================================
     * RELASI KE USER (KARYAWAN)
     * ======================================================
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'job_todo_user')
            ->using(JobTodoUser::class)
            ->withPivot([
                'status',        // pending | accepted | completed
                'completed_at',
            ])
            ->withTimestamps();
    }

    /**
     * ======================================================
     * SCOPE: JOB MASIH AKTIF
     * ======================================================
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * ======================================================
     * HELPER STATUS LABEL
     * ======================================================
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open'   => 'Aktif',
            'closed' => 'Ditutup',
            default  => ucfirst($this->status),
        };
    }
}
