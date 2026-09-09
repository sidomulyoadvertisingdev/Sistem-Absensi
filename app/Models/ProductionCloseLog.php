<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionCloseLog extends Model
{
    protected $fillable = [
        'job_todo_id',
        'user_id',
        'erp_item_id',
        'erp_order_id',
        'invoice_no',
        'outlet_id',
        'order_completed',
        'note',
        'foto_path',
        'latitude',
        'longitude',
        'accuracy',
        'captured_at',
        'erp_response',
    ];

    protected $casts = [
        'order_completed' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'accuracy' => 'float',
        'captured_at' => 'datetime',
        'erp_response' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobTodo(): BelongsTo
    {
        return $this->belongsTo(JobTodo::class);
    }
}
