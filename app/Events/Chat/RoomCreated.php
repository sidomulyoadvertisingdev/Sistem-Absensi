<?php

namespace App\Events\Chat;

use App\Models\ChatRoom;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class RoomCreated implements ShouldBroadcastNow
{
    use SerializesModels;

    /**
     * @param  array<int>  $memberIds
     */
    public function __construct(
        public ChatRoom $room,
        public array $memberIds
    ) {
        //
    }

    public function broadcastOn(): array
    {
        return collect($this->memberIds)
            ->map(fn ($id) => new PrivateChannel('chat.user.' . $id))
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'room:created';
    }

    public function broadcastWith(): array
    {
        return [
            'room' => [
                'id' => $this->room->id,
                'name' => $this->room->name,
                'is_group' => $this->room->is_group,
            ],
        ];
    }
}
