<?php

namespace App\Events;

use App\Models\JobTodo;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobTodoCreated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * Job Todo data
     */
    public JobTodo $jobTodo;

    /**
     * Target user (karyawan)
     */
    public int $userId;

    /**
     * ==================================================
     * CONSTRUCTOR
     * ==================================================
     */
    public function __construct(JobTodo $jobTodo, int $userId)
    {
        $this->jobTodo = $jobTodo;
        $this->userId  = $userId;
    }

    /**
     * ==================================================
     * CHANNEL (PRIVATE)
     * job-todo.{userId}
     * ==================================================
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('job-todo.' . $this->userId);
    }

    /**
     * ==================================================
     * EVENT NAME
     * ==================================================
     */
    public function broadcastAs(): string
    {
        return 'job.todo.created';
    }

    /**
     * ==================================================
     * DATA YANG DIKIRIM KE FRONTEND
     * ==================================================
     */
    public function broadcastWith(): array
    {
        return [
            'id'     => $this->jobTodo->id,
            'title'  => $this->jobTodo->title,
            'bonus'  => $this->jobTodo->bonus,
            'type'   => $this->jobTodo->broadcast ? 'broadcast' : 'direct',
            'status' => $this->jobTodo->status,
        ];
    }
}
