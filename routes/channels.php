<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Job Todo Channel (PRIVATE)
|--------------------------------------------------------------------------
| Hanya user yang bersangkutan yang boleh menerima notif job
| Channel: job-todo.{userId}
*/
Broadcast::channel('job-todo.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
