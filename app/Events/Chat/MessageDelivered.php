<?php

namespace App\Events\Chat;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageDelivered implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public int $roomId,
        public array $messageIds
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
        return 'message:delivered';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->roomId,
            'message_ids' => $this->messageIds,
        ];
    }
}
