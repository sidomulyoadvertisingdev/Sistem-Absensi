<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationWebhook extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'webhook_url',
        'secret',
        'events',
        'is_active',
        'last_error',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscribes(string $event): bool
    {
        return in_array($event, $this->events ?? [], true);
    }
}
