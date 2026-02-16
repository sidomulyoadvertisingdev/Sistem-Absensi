<?php

namespace App\Events\Chatify;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $payload,
        public string $roomType,
        public int $roomId,
        public int $fromId,
        public int $toId
    ) {}

    public function broadcastOn(): array
    {
        if ($this->roomType === 'group') {
            return [new PrivateChannel('chatify.group.' . $this->roomId)];
        }

        return [
            new PrivateChannel('chatify.user.' . $this->toId),
            new PrivateChannel('chatify.user.' . $this->fromId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'chatify.typing';
    }
}
