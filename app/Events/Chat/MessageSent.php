<?php

namespace App\Events\Chat;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public ChatMessage $message,
        public ?string $tempId,
        public array $unreadTotals
    ) {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.room.' . $this->message->room_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message:new';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'room_id' => $this->message->room_id,
            'text' => $this->message->text,
            'sender_id' => $this->message->user_id,
            'created_at' => $this->message->created_at?->copy()->utc()->toIso8601String(),
            'temp_id' => $this->tempId,
            'unread_totals' => $this->unreadTotals,
        ];
    }
}
