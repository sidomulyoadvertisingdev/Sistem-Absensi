<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicant extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'name',
        'email',
        'phone',
        'answers',
        'status',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    /**
     * 🔗 Job yang dilamar
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * 🔗 User pelamar
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
}
