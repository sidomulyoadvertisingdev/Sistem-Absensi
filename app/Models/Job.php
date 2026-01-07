<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    /**
     * Table name
     */
    protected $table = 'job_vacancies';

    /**
     * Mass assignable fields
     */
    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'location',
        'job_type',
        'deadline',
        'is_active',
    ];

    /**
     * Append attribute otomatis ke JSON
     */
    protected $appends = [
        'share_url',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'deadline'  => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke form persyaratan job
     */
    public function formFields()
    {
        return $this->hasMany(JobFormField::class, 'job_id');
    }

    /**
     * 🔥 SHARE URL (AUTO)
     */
    public function getShareUrlAttribute(): string
    {
        return config('app.frontend_url') . '/jobs/' . $this->id;
    }
}
