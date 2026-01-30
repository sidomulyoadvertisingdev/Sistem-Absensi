<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('job-todo.{userId}', function ($user, $userId) {

    if (! $user) {
        return false; // ⛔ belum login → 403
    }

    return (int) $user->id === (int) $userId;
});
