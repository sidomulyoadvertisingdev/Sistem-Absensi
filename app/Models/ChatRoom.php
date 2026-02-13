<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_group',
        'owner_id',
    ];

    protected $casts = [
        'is_group' => 'boolean',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        // pivot uses column room_id (not chat_room_id)
        return $this->belongsToMany(User::class, 'chat_room_user', 'room_id', 'user_id')
            ->withPivot(['role', 'unread_count', 'last_read_message_id'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'room_id');
    }

    public function latestMessage(): HasOne
    {
        // Explicitly qualify columns to avoid ambiguity with latestOfMany join
        return $this->hasOne(ChatMessage::class, 'room_id')
            ->latestOfMany('id')
            ->select([
                'chat_messages.id',
                'chat_messages.room_id',
                'chat_messages.user_id',
                'chat_messages.text',
                'chat_messages.created_at',
            ]);
    }
}
