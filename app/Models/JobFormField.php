<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobFormField extends Model
{
    protected $fillable = [
        'job_id',
        'label',
        'name',
        'type',
        'required',
    ];

    protected $casts = [
        'required' => 'boolean',
    ];

    /**
     * Relasi ke Job
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}
