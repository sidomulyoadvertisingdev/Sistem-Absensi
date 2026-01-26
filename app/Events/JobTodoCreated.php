<?php

namespace App\Events;

use App\Models\JobTodo;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobTodoCreated
{
    use Dispatchable, SerializesModels;

    public JobTodo $jobTodo;
    public ?int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(JobTodo $jobTodo, ?int $userId = null)
    {
        $this->jobTodo = $jobTodo;
        $this->userId  = $userId;
    }
}
