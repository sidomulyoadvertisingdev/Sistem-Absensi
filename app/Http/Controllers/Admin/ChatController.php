<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role', 'profile_photo']);

        $rooms = auth()->user()->chatRooms()
            ->with([
                'members:id,name,role',
                'latestMessage' => fn ($query) => $query->select(
                    'chat_messages.id',
                    'chat_messages.room_id',
                    'chat_messages.text',
                    'chat_messages.created_at'
                ),
            ])
            ->orderByDesc('chat_rooms.updated_at')
            ->get();

        return view('admin.chat.index', compact('users', 'rooms'));
    }

    public function startDirect(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $actor = $request->user();
        $targetId = (int) $request->integer('user_id');

        if ($actor->id === $targetId) {
            return back()->withErrors(['user_id' => 'Tidak bisa chat dengan diri sendiri']);
        }

        $room = ChatRoom::where('is_group', false)
            ->whereHas('members', fn ($q) => $q->whereIn('users.id', [$actor->id, $targetId]))
            ->withCount('members')
            ->having('members_count', 2)
            ->first();

        if (! $room) {
            DB::transaction(function () use (&$room, $actor, $targetId) {
                $room = ChatRoom::create([
                    'name' => 'Direct Chat',
                    'is_group' => false,
                    'owner_id' => null,
                ]);

                $room->members()->sync([
                    $actor->id => ['role' => 'owner', 'unread_count' => 0],
                    $targetId => ['role' => 'member', 'unread_count' => 0],
                ]);
            });
        }

        return redirect()->route('admin.chat')->with('success', 'Chat siap, buka di aplikasi/FE.');
    }

    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'integer|exists:users,id',
        ]);

        $actor = $request->user();

        $memberIds = collect($request->input('member_ids', []))
            ->push($actor->id)
            ->unique()
            ->values();

        DB::transaction(function () use ($actor, $memberIds, $request) {
            $room = ChatRoom::create([
                'name' => (string) $request->string('name'),
                'is_group' => true,
                'owner_id' => $actor->id,
            ]);

            $room->members()->sync($memberIds->mapWithKeys(function ($id) use ($actor) {
                return [$id => [
                    'role' => $id === $actor->id ? 'owner' : 'member',
                    'unread_count' => 0,
                ]];
            })->toArray());
        });

        return redirect()->route('admin.chat')->with('success', 'Grup chat berhasil dibuat.');
    }
}
