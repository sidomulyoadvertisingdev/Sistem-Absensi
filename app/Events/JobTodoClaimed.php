<?php

namespace App\Events;

use App\Models\JobTodo;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobTodoClaimed
{
    use Dispatchable, SerializesModels;

    public JobTodo $jobTodo;
    public int $userId;

    public function __construct(JobTodo $jobTodo, int $userId)
    {
        $this->jobTodo = $jobTodo;
        $this->userId = $userId;
    }
}
