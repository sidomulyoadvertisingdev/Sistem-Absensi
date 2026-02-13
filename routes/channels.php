<?php

use App\Models\ChatRoom;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('job-todo.{userId}', function ($user, $userId) {
    if (! $user) {
        return false;
    }

    return (int) $user->id === (int) $userId;
});

Broadcast::channel('chat.room.{roomId}', function ($user, $roomId) {
    return ChatRoom::where('id', $roomId)
        ->whereHas('members', fn ($q) => $q->where('users.id', $user->id))
        ->exists();
});

Broadcast::channel('chat.user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
