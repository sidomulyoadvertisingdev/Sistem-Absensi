<?php

namespace App\Events\Chat;

use App\Models\ChatRoom;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class RoomMemberRemoved implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public ChatRoom $room,
        public int $userId
    ) {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.room.' . $this->room->id),
            new PrivateChannel('chat.user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'room:member_removed';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->room->id,
            'user_id' => $this->userId,
        ];
    }
}
