<?php

namespace App\Events;

use App\Models\JobTodo;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class JobTodoDone implements ShouldBroadcastNow
{
    use SerializesModels;

    public JobTodo $job;
    public User $user;

    public function __construct(JobTodo $job, User $user)
    {
        $this->job  = $job;
        $this->user = $user;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('job-todo');
    }

    public function broadcastAs(): string
    {
        return 'job.todo.done';
    }

    public function broadcastWith(): array
    {
        return [
            'job_id' => $this->job->id,
            'title'  => $this->job->title,
            'user'   => $this->user->name,
            'bonus'  => $this->job->bonus,
        ];
    }
}
