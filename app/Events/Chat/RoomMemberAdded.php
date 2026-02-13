<?php

namespace App\Events\Chat;

use App\Models\ChatRoom;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class RoomMemberAdded implements ShouldBroadcastNow
{
    use SerializesModels;

    /**
     * @param  array<int>  $userIds
     */
    public function __construct(
        public ChatRoom $room,
        public array $userIds
    ) {
        //
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('chat.room.' . $this->room->id),
        ];

        foreach ($this->userIds as $id) {
            $channels[] = new PrivateChannel('chat.user.' . $id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'room:member_added';
    }

    public function broadcastWith(): array
    {
        return [
            'room_id' => $this->room->id,
            'user_ids' => $this->userIds,
        ];
    }
}
