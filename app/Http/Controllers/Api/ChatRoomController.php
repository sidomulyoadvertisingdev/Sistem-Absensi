<?php

namespace App\Http\Controllers\Api;

use App\Events\Chat\RoomCreated;
use App\Events\Chat\RoomMemberAdded;
use App\Events\Chat\RoomMemberRemoved;
use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatRoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $rooms = $user->chatRooms()
            ->with([
                'members:id,name,profile_photo',
                'latestMessage' => fn ($query) => $query->select(
                    'chat_messages.id',
                    'chat_messages.room_id',
                    'chat_messages.user_id',
                    'chat_messages.text',
                    'chat_messages.created_at'
                ),
            ])
            ->orderByDesc('chat_rooms.updated_at')
            ->get();

        $payload = $rooms->map(function (ChatRoom $room) {
            return [
                'id' => $room->id,
                'name' => $room->name,
                'is_group' => $room->is_group,
                'members' => $room->members->map(fn (User $member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'profile_photo' => $member->profile_photo,
                ])->values(),
                'unread_count' => (int) ($room->pivot?->unread_count ?? 0),
                'last_message' => $room->latestMessage
                    ? [
                        'id' => $room->latestMessage->id,
                        'text' => $room->latestMessage->text,
                        'sender_id' => $room->latestMessage->user_id,
                        'created_at' => $room->latestMessage->created_at?->copy()->utc()->toIso8601String(),
                    ]
                    : null,
            ];
        });

        return response()->json($payload);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'integer|exists:users,id',
        ]);

        $user = $request->user();

        if (! $this->userCanManageGroup($user)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $memberIds = collect($request->input('member_ids', []))
            ->push($user->id)
            ->unique()
            ->values();

        $room = null;

        DB::transaction(function () use (&$room, $user, $memberIds, $request) {
            $room = ChatRoom::create([
                'name' => (string) $request->string('name'),
                'is_group' => true,
                'owner_id' => $user->id,
            ]);

            $attach = $memberIds->mapWithKeys(function (int $memberId) use ($user) {
                $role = $memberId === $user->id ? 'owner' : 'member';
                return [$memberId => [
                    'role' => $role,
                    'unread_count' => 0,
                ]];
            });

            $room->members()->syncWithoutDetaching($attach->toArray());
        });

        $room->load(['members:id,name,profile_photo', 'latestMessage']);

        broadcast(new RoomCreated($room, $memberIds->all()))->toOthers();

        return response()->json([
            'id' => $room->id,
            'name' => $room->name,
            'is_group' => $room->is_group,
            'members' => $room->members->map(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'profile_photo' => $member->profile_photo,
            ])->values(),
            'unread_count' => 0,
            'last_message' => null,
        ], 201);
    }

    public function addMembers(Request $request, ChatRoom $room): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $actor = $request->user();

        if (! $this->userCanAdminRoom($actor, $room)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! $room->is_group) {
            return response()->json(['message' => 'Hanya untuk grup'], 422);
        }

        $userIds = collect($request->input('user_ids', []))->unique()->values();

        DB::transaction(function () use ($room, $userIds) {
            $payload = $userIds->mapWithKeys(fn (int $id) => [
                $id => [
                    'role' => 'member',
                    'unread_count' => 0,
                ],
            ]);

            $room->members()->syncWithoutDetaching($payload->toArray());
        });

        broadcast(new RoomMemberAdded($room, $userIds->all()))->toOthers();

        return response()->json(['message' => 'Members added']);
    }

    public function removeMember(Request $request, ChatRoom $room, int $userId): JsonResponse
    {
        $actor = $request->user();

        if (! $this->userCanAdminRoom($actor, $room)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if (! $room->is_group) {
            return response()->json(['message' => 'Hanya untuk grup'], 422);
        }

        if ((int) $room->owner_id === (int) $userId) {
            return response()->json(['message' => 'Tidak bisa menghapus owner'], 422);
        }

        $room->members()->detach($userId);

        broadcast(new RoomMemberRemoved($room, $userId))->toOthers();

        return response()->json(['message' => 'Member removed']);
    }

    private function userCanManageGroup(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin() || $user->isAdminStaff();
    }

    private function userCanAdminRoom(User $user, ChatRoom $room): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if ((int) $room->owner_id === (int) $user->id) {
            return true;
        }

        $role = $room->members()
            ->where('users.id', $user->id)
            ->value('chat_room_user.role');

        return in_array($role, ['owner', 'admin'], true);
    }
}
