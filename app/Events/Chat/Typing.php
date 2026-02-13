<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class Typing implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public int $roomId,
        public int $userId,
        public bool $isTyping
    ) {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.room.' . $this->roomId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'typing';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->roomId,
            'user_id' => $this->userId,
            'isTyping' => $this->isTyping,
        ];
    }
}
