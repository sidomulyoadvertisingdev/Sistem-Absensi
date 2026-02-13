<?php

namespace App\Http\Controllers\Api;

use App\Events\Chat\MessageDelivered;
use App\Events\Chat\MessageSeen;
use App\Events\Chat\MessageSent;
use App\Events\Chat\Typing;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatMessageReceipt;
use App\Models\ChatRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ChatMessageController extends Controller
{
    public function index(Request $request, ChatRoom $room): JsonResponse
    {
        $user = $request->user();

        if (! $this->userIsMember($user->id, $room)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $limit = (int) $request->integer('limit', 20);
        $limit = max(1, min($limit, 50));
        $cursor = $request->input('cursor');

        $query = ChatMessage::where('room_id', $room->id)
            ->with([
                'sender:id,name,profile_photo',
                'receipts' => fn ($q) => $q->where('user_id', $user->id),
            ]);

        if ($cursor) {
            if (ctype_digit((string) $cursor)) {
                $query->where('id', '<', (int) $cursor);
            } else {
                try {
                    $cursorDate = Carbon::parse($cursor);
                    $query->where('created_at', '<', $cursorDate);
                } catch (\Throwable $th) {
                    // ignore invalid cursor
                }
            }
        }

        $messages = $query
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        $payload = $messages->map(function (ChatMessage $message) use ($user) {
            $receipt = $message->receipts->first();
            $isOwn = (int) $message->user_id === (int) $user->id;

            return [
                'id' => $message->id,
                'room_id' => $message->room_id,
                'text' => $message->text,
                'sender_id' => $message->user_id,
                'delivered' => $isOwn ? true : (bool) ($receipt?->delivered_at),
                'seen' => $isOwn ? true : (bool) ($receipt?->seen_at),
                'created_at' => $message->created_at?->copy()->utc()->toIso8601String(),
            ];
        });

        return response()->json($payload);
    }

    public function store(Request $request, ChatRoom $room): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:2000',
            'temp_id' => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        if (! $this->userIsMember($user->id, $room)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $text = trim(strip_tags($request->input('text', '')));

        if ($text === '') {
            return response()->json(['message' => 'Pesan kosong'], 422);
        }

        $tempId = $request->input('temp_id');
        $message = null;
        $unreadTotals = [];
        $recipientIds = collect();

        DB::transaction(function () use (&$message, &$unreadTotals, &$recipientIds, $user, $room, $text) {
            $message = ChatMessage::create([
                'room_id' => $room->id,
                'user_id' => $user->id,
                'text' => $text,
            ]);

            $room->touch();

            $recipientIds = $room->members()
                ->where('users.id', '!=', $user->id)
                ->pluck('users.id');

            if ($recipientIds->isNotEmpty()) {
                DB::table('chat_room_user')
                    ->where('room_id', $room->id)
                    ->whereIn('user_id', $recipientIds)
                    ->increment('unread_count', 1);

                $now = now();

                $receiptsPayload = $recipientIds->map(function ($recipientId) use ($message, $now) {
                    return [
                        'message_id' => $message->id,
                        'user_id' => $recipientId,
                        'delivered_at' => null,
                        'seen_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })->values()->all();

                ChatMessageReceipt::insert($receiptsPayload);

                $unreadTotals = DB::table('chat_room_user')
                    ->whereIn('user_id', $recipientIds)
                    ->select('user_id', DB::raw('SUM(unread_count) as total'))
                    ->groupBy('user_id')
                    ->pluck('total', 'user_id')
                    ->map(fn ($total) => (int) $total)
                    ->toArray();
            }
        });

        broadcast(new MessageSent($message, $tempId, $unreadTotals))->toOthers();

        return response()->json([
            'id' => $message->id,
            'room_id' => $message->room_id,
            'text' => $message->text,
            'sender_id' => $message->user_id,
            'created_at' => $message->created_at?->copy()->utc()->toIso8601String(),
            'temp_id' => $tempId,
        ], 201);
    }

    public function markRead(Request $request, ChatRoom $room): JsonResponse
    {
        $request->validate([
            'last_message_id' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $lastMessageId = (int) $request->input('last_message_id');

        if (! $this->userIsMember($user->id, $room)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        DB::transaction(function () use ($user, $room, $lastMessageId) {
            DB::table('chat_room_user')
                ->where('room_id', $room->id)
                ->where('user_id', $user->id)
                ->update([
                    'unread_count' => 0,
                    'last_read_message_id' => $lastMessageId,
                    'updated_at' => now(),
                ]);

            DB::table('chat_message_receipts')
                ->join('chat_messages', 'chat_messages.id', '=', 'chat_message_receipts.message_id')
                ->where('chat_messages.room_id', $room->id)
                ->where('chat_message_receipts.user_id', $user->id)
                ->where('chat_messages.id', '<=', $lastMessageId)
                ->whereNull('chat_message_receipts.seen_at')
                ->update([
                    'chat_message_receipts.seen_at' => now(),
                    'chat_message_receipts.updated_at' => now(),
                ]);
        });

        broadcast(new MessageSeen($room->id, $user->id, $lastMessageId))->toOthers();

        return response()->json(['message' => 'Read status updated']);
    }

    public function markDelivered(Request $request, ChatRoom $room): JsonResponse
    {
        $request->validate([
            'message_ids' => 'required|array|min:1',
            'message_ids.*' => 'integer|min:1',
        ]);

        $user = $request->user();

        if (! $this->userIsMember($user->id, $room)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $messageIds = collect($request->input('message_ids', []))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        DB::table('chat_message_receipts')
            ->join('chat_messages', 'chat_messages.id', '=', 'chat_message_receipts.message_id')
            ->where('chat_messages.room_id', $room->id)
            ->where('chat_message_receipts.user_id', $user->id)
            ->whereIn('chat_messages.id', $messageIds)
            ->whereNull('chat_message_receipts.delivered_at')
            ->update([
                'chat_message_receipts.delivered_at' => now(),
                'chat_message_receipts.updated_at' => now(),
            ]);

        broadcast(new MessageDelivered($room->id, $messageIds->all()))->toOthers();

        return response()->json(['message' => 'Delivered status updated']);
    }

    public function typing(Request $request, ChatRoom $room): JsonResponse
    {
        $request->validate([
            'isTyping' => 'required|boolean',
        ]);

        $user = $request->user();

        if (! $this->userIsMember($user->id, $room)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        broadcast(new Typing($room->id, $user->id, (bool) $request->boolean('isTyping')))->toOthers();

        return response()->json(['message' => 'ok']);
    }

    private function userIsMember(int $userId, ChatRoom $room): bool
    {
        return $room->members()
            ->where('users.id', $userId)
            ->exists();
    }
}
