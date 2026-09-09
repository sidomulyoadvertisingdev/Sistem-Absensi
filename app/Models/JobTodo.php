<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobTodo extends Model
{
    protected $table = 'job_todos';

    public const TYPE_GENERAL = 'general';
    public const TYPE_PRODUCTION = 'production';

    protected $fillable = [
        'title',
        'description',
        'bonus',
        'broadcast',
        'status', // open | in_progress | done | closed

        // Job produksi (jembatan ke item produksi ERP)
        'type',              // general | production
        'source',            // pos
        'erp_item_id',
        'erp_order_id',
        'invoice_no',
        'outlet_id',
        'customer_name',
        'customer_phone',
        'product_name',
        'qty',
        'deadline_at',
        'production_status', // in_production | completed (cermin ERP)
        'spec_summary',
    ];

    protected $casts = [
        'broadcast'    => 'boolean',
        'bonus'        => 'integer',
        'erp_item_id'  => 'integer',
        'erp_order_id' => 'integer',
        'outlet_id'    => 'integer',
        'deadline_at'  => 'datetime',
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

    public function closeLogs(): HasMany
    {
        return $this->hasMany(ProductionCloseLog::class);
    }

    /**
     * ======================================================
     * SCOPES
     * ======================================================
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeProduction($query)
    {
        return $query->where('type', self::TYPE_PRODUCTION);
    }

    public function isProduction(): bool
    {
        return $this->type === self::TYPE_PRODUCTION;
    }

    /**
     * ======================================================
     * HELPER STATUS LABEL
     * ======================================================
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open'        => 'Aktif',
            'in_progress' => 'Dikerjakan',
            'done'        => 'Selesai',
            'closed'      => 'Ditutup',
            default       => ucfirst((string) $this->status),
        };
    }
}
