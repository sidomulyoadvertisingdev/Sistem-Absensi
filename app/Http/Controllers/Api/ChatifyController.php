<?php

namespace App\Http\Controllers\Api;

use App\Events\Chatify\ChatMessageSent;
use App\Events\Chatify\ChatRead;
use App\Events\Chatify\ChatTyping;
use App\Http\Controllers\Controller;
use App\Models\ChGroup;
use App\Models\ChMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatifyController extends Controller
{
    public function rooms(Request $request)
    {
        $user = $request->user();

        $directUnread = DB::table('ch_messages')
            ->select('from_id', DB::raw('count(*) as cnt'))
            ->where('to_id', $user->id)
            ->where('to_type', 'user')
            ->where('seen', false)
            ->groupBy('from_id')
            ->pluck('cnt', 'from_id');

        $groupUnread = DB::table('ch_messages')
            ->leftJoin('ch_message_reads', function ($join) use ($user) {
                $join->on('ch_messages.id', '=', 'ch_message_reads.message_id')
                    ->where('ch_message_reads.user_id', $user->id);
            })
            ->where('ch_messages.to_type', 'group')
            ->whereNull('ch_message_reads.read_at')
            ->where('ch_messages.from_id', '!=', $user->id)
            ->select('ch_messages.to_id', DB::raw('count(*) as cnt'))
            ->groupBy('ch_messages.to_id')
            ->pluck('cnt', 'ch_messages.to_id');

        $recentMessages = ChMessage::query()
            ->where(function ($q) use ($user) {
                $q->where('from_id', $user->id)
                    ->orWhere(function ($q) use ($user) {
                        $q->where('to_id', $user->id)
                            ->where('to_type', 'user');
                    });
            })
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $directRooms = [];
        foreach ($recentMessages as $msg) {
            $partnerId = $msg->from_id == $user->id ? $msg->to_id : $msg->from_id;
            if (isset($directRooms[$partnerId])) {
                continue;
            }
            $partner = User::find($partnerId);
            if (! $partner) {
                continue;
            }
        $directRooms[$partnerId] = [
            'id' => (int) $partnerId,
            'type' => 'direct',
            'name' => $partner->name,
            'last_message' => $this->transformMessage($msg, [
                $msg->from_id => $msg->from_id == $user->id ? $user->name : $partner->name,
            ]),
            'unread_count' => (int) ($directUnread[$partnerId] ?? 0),
        ];
        }

        $groups = DB::table('ch_groups')
            ->join('ch_group_user', 'ch_groups.id', '=', 'ch_group_user.group_id')
            ->where('ch_group_user.user_id', $user->id)
            ->select('ch_groups.*')
            ->get();

        $groupRooms = [];
        foreach ($groups as $group) {
            $last = ChMessage::query()
                ->where('to_type', 'group')
                ->where('to_id', $group->id)
                ->orderByDesc('created_at')
                ->first();
            $groupRooms[] = [
                'id' => (int) $group->id,
                'type' => 'group',
                'name' => $group->name,
                'last_message' => $last ? $this->transformMessage($last) : null,
                'unread_count' => (int) ($groupUnread[$group->id] ?? 0),
            ];
        }

        $rooms = array_values($directRooms);
        $rooms = array_merge($rooms, $groupRooms);
        usort($rooms, function ($a, $b) {
            $aTime = $a['last_message']['created_at'] ?? null;
            $bTime = $b['last_message']['created_at'] ?? null;
            return strcmp((string) $bTime, (string) $aTime);
        });

        return response()->json(['data' => $rooms]);
    }

    public function users(Request $request)
    {
        $user = $request->user();

        $users = User::query()
            ->where('id', '!=', $user->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $users]);
    }

    public function createRoom(Request $request)
    {
        $user = $request->user();
        $type = $request->input('type', 'direct');

        if ($type === 'group') {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'member_ids' => ['required', 'array', 'min:1'],
                'member_ids.*' => ['integer'],
            ]);

            $group = ChGroup::create([
                'name' => $request->input('name'),
                'created_by' => $user->id,
            ]);

            $memberIds = collect($request->input('member_ids'))->map(fn ($id) => (int) $id);
            $memberIds->push($user->id);
            $memberIds = $memberIds->unique();

            $rows = $memberIds->map(function ($id) use ($group, $user) {
                return [
                    'group_id' => $group->id,
                    'user_id' => $id,
                    'role' => $id === $user->id ? 'admin' : 'member',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            DB::table('ch_group_user')->insert($rows);

            return response()->json([
                'data' => [
                    'id' => (int) $group->id,
                    'type' => 'group',
                    'name' => $group->name,
                ],
            ]);
        }

        $request->validate([
            'user_id' => ['required', 'integer'],
        ]);

        $other = User::findOrFail((int) $request->input('user_id'));

        return response()->json([
            'data' => [
                'id' => (int) $other->id,
                'type' => 'direct',
                'name' => $other->name,
            ],
        ]);
    }

    public function messages(Request $request, int $roomId)
    {
        $user = $request->user();
        $type = $request->input('type', 'direct');

        if ($type === 'group') {
            $this->assertGroupMember($roomId, $user->id);

            $messages = ChMessage::query()
                ->where('to_type', 'group')
                ->where('to_id', $roomId)
                ->orderBy('created_at')
                ->get();

            $usersById = User::query()
                ->whereIn('id', $messages->pluck('from_id')->unique())
                ->pluck('name', 'id')
                ->all();

            return response()->json([
                'data' => $messages->map(fn ($m) => $this->transformMessage($m, $usersById)),
            ]);
        }

        $messages = ChMessage::query()
            ->where(function ($q) use ($user, $roomId) {
                $q->where('from_id', $user->id)
                    ->where('to_id', $roomId)
                    ->where('to_type', 'user');
            })
            ->orWhere(function ($q) use ($user, $roomId) {
                $q->where('from_id', $roomId)
                    ->where('to_id', $user->id)
                    ->where('to_type', 'user');
            })
            ->orderBy('created_at')
            ->get();

        $usersById = User::query()
            ->whereIn('id', $messages->pluck('from_id')->unique())
            ->pluck('name', 'id')
            ->all();

        return response()->json([
            'data' => $messages->map(fn ($m) => $this->transformMessage($m, $usersById)),
        ]);
    }

    public function sendMessage(Request $request, int $roomId)
    {
        $user = $request->user();
        $type = $request->input('type', 'direct');
        $text = (string) $request->input('text', '');

        $request->validate([
            'text' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $attachment = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('chatify/attachments', 'public');
            $attachment = $path ? Storage::url($path) : null;
        }

        if ($text === '' && ! $attachment) {
            return response()->json(['message' => 'Empty message'], 422);
        }

        if ($type === 'group') {
            $this->assertGroupMember($roomId, $user->id);
        }

        $message = ChMessage::create([
            'id' => (string) Str::uuid(),
            'from_id' => $user->id,
            'to_id' => $roomId,
            'to_type' => $type === 'group' ? 'group' : 'user',
            'body' => $text,
            'attachment' => $attachment,
            'seen' => false,
        ]);

        $payload = $this->transformMessage($message, [$user->id => $user->name]);
        $payload['room_type'] = $type === 'group' ? 'group' : 'direct';
        $payload['room_id'] = $roomId;

        event(new ChatMessageSent(
            $payload,
            $type === 'group' ? 'group' : 'direct',
            $roomId,
            $user->id,
            $roomId
        ));

        return response()->json(['data' => $payload]);
    }

    public function read(Request $request, int $roomId)
    {
        $user = $request->user();
        $type = $request->input('type', 'direct');

        if ($type === 'group') {
            $this->assertGroupMember($roomId, $user->id);

            $messageIds = ChMessage::query()
                ->where('to_type', 'group')
                ->where('to_id', $roomId)
                ->where('from_id', '!=', $user->id)
                ->pluck('id');

            $rows = $messageIds->map(function ($id) use ($user) {
                return [
                    'message_id' => $id,
                    'user_id' => $user->id,
                    'read_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            if (! empty($rows)) {
                DB::table('ch_message_reads')->upsert(
                    $rows,
                    ['message_id', 'user_id'],
                    ['read_at', 'updated_at']
                );
            }

            event(new ChatRead(
                ['room_id' => $roomId, 'type' => 'group', 'user_id' => $user->id],
                'group',
                $roomId,
                $user->id,
                $roomId
            ));

            return response()->json(['status' => 'ok']);
        }

        ChMessage::query()
            ->where('from_id', $roomId)
            ->where('to_id', $user->id)
            ->where('to_type', 'user')
            ->update(['seen' => true]);

        event(new ChatRead(
            ['room_id' => $roomId, 'type' => 'direct', 'user_id' => $user->id],
            'direct',
            $roomId,
            $user->id,
            $roomId
        ));

        return response()->json(['status' => 'ok']);
    }

    public function typing(Request $request, int $roomId)
    {
        $user = $request->user();
        $type = $request->input('type', 'direct');
        $isTyping = (bool) $request->input('is_typing', false);

        if ($type === 'group') {
            $this->assertGroupMember($roomId, $user->id);
        }

        event(new ChatTyping(
            [
                'room_id' => $roomId,
                'type' => $type === 'group' ? 'group' : 'direct',
                'user_id' => $user->id,
                'user_name' => $user->name,
                'is_typing' => $isTyping,
            ],
            $type === 'group' ? 'group' : 'direct',
            $roomId,
            $user->id,
            $roomId
        ));

        return response()->json(['status' => 'ok']);
    }

    private function assertGroupMember(int $groupId, int $userId): void
    {
        $exists = DB::table('ch_group_user')
            ->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->exists();

        abort_if(! $exists, 403, 'Not a group member');
    }

    private function transformMessage(ChMessage $message, array $usersById = []): array
    {
        $fromName = $usersById[$message->from_id] ?? User::query()
            ->where('id', $message->from_id)
            ->value('name');

        return [
            'id' => $message->id,
            'from_id' => (int) $message->from_id,
            'from_name' => $fromName,
            'to_id' => (int) $message->to_id,
            'to_type' => $message->to_type ?? 'user',
            'body' => $message->body,
            'attachment' => $message->attachment,
            'seen' => (bool) $message->seen,
            'created_at' => $message->created_at?->toISOString(),
        ];
    }
}
