<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageSeen implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public int $roomId,
        public int $userId,
        public int $lastMessageId
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
        return 'message:seen';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->roomId,
            'user_id' => $this->userId,
            'last_message_id' => $this->lastMessageId,
        ];
    }
}
