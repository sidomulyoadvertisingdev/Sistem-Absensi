<?php

namespace App\Http\Controllers\Chatify;

use App\Http\Controllers\Controller;
use App\Models\ChGroup;
use App\Models\ChMessage as Message;
use App\Models\User;
use Chatify\Facades\ChatifyMessenger as Chatify;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class MessagesController extends Controller
{
    protected $perPage = 30;

    public function index(Request $request, $id = null)
    {
        $messenger_color = Auth::user()->messenger_color;
        $type = $request->route()?->getName() === 'group' ? 'group' : 'user';
        $users = User::query()
            ->where('id', '!=', Auth::id())
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('Chatify::pages.app', [
            'id' => $id ?? 0,
            'type' => $id ? $type : 'user',
            'chatifyUsers' => $users,
            'messengerColor' => $messenger_color ? $messenger_color : Chatify::getFallbackColor(),
            'dark_mode' => Auth::user()->dark_mode < 1 ? 'light' : 'dark',
        ]);
    }

    public function pusherAuth(Request $request): JsonResponse
    {
        return Chatify::pusherAuth(
            $request->user(),
            Auth::user(),
            $request['channel_name'],
            $request['socket_id']
        );
    }

    public function idFetchData(Request $request): JsonResponse
    {
        $type = $this->resolveType($request);
        $id = (int) $request['id'];

        if ($type === 'group') {
            $group = ChGroup::query()->find($id);
            if (! $group) {
                return Response::json([
                    'favorite' => 0,
                    'fetch' => null,
                    'user_avatar' => null,
                ]);
            }

            $this->assertGroupMember($group->id, Auth::id());

            return Response::json([
                'favorite' => 0,
                'fetch' => (object) [
                    'id' => $group->id,
                    'name' => $group->name,
                    'type' => 'group',
                ],
                'user_avatar' => $this->groupAvatar(),
            ]);
        }

        $favorite = Chatify::inFavorite($id);
        $fetch = User::where('id', $id)->first();
        if ($fetch) {
            $userAvatar = Chatify::getUserWithAvatar($fetch)->avatar;
        }

        return Response::json([
            'favorite' => $favorite,
            'fetch' => $fetch ?? null,
            'user_avatar' => $userAvatar ?? null,
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $type = $this->resolveType($request);
        $id = (int) $request['id'];

        $error = (object) [
            'status' => 0,
            'message' => null,
        ];
        $attachment = null;
        $attachment_title = null;

        if ($request->hasFile('file')) {
            $allowed_images = Chatify::getAllowedImages();
            $allowed_files = Chatify::getAllowedFiles();
            $allowed = array_merge($allowed_images, $allowed_files);

            $file = $request->file('file');
            if ($file->getSize() < Chatify::getMaxUploadSize()) {
                if (in_array(strtolower($file->extension()), $allowed)) {
                    $attachment_title = $file->getClientOriginalName();
                    $attachment = Str::uuid() . '.' . $file->extension();
                    $file->storeAs(
                        config('chatify.attachments.folder'),
                        $attachment,
                        config('chatify.storage_disk_name')
                    );
                } else {
                    $error->status = 1;
                    $error->message = 'File extension not allowed!';
                }
            } else {
                $error->status = 1;
                $error->message = 'File size you are trying to upload is too large!';
            }
        }

        if (! $error->status) {
            if ($type === 'group') {
                $this->assertGroupMember($id, Auth::id());
            }

            $message = Message::create([
                'id' => (string) Str::uuid(),
                'from_id' => Auth::id(),
                'to_id' => $id,
                'to_type' => $type === 'group' ? 'group' : 'user',
                'body' => htmlentities(trim($request['message']), ENT_QUOTES, 'UTF-8'),
                'attachment' => $attachment
                    ? json_encode((object) [
                        'new_name' => $attachment,
                        'old_name' => htmlentities(trim($attachment_title), ENT_QUOTES, 'UTF-8'),
                    ])
                    : null,
                'seen' => false,
            ]);

            $messageData = Chatify::parseMessage($message);
            $messageData['room_type'] = $type;
            $messageData['from_name'] = Auth::user()->name;

            if ($type === 'group') {
                Chatify::push("private-chatify.group.$id", 'messaging', [
                    'from_id' => Auth::id(),
                    'to_id' => $id,
                    'to_type' => 'group',
                    'message' => Chatify::messageCard($messageData, true),
                ]);
            } elseif (Auth::id() !== $id) {
                Chatify::push("private-chatify." . $id, 'messaging', [
                    'from_id' => Auth::id(),
                    'to_id' => $id,
                    'message' => Chatify::messageCard($messageData, true),
                ]);
            }
        }

        return Response::json([
            'status' => '200',
            'error' => $error,
            'message' => Chatify::messageCard(@$messageData),
            'tempID' => $request['temporaryMsgId'],
        ]);
    }

    public function fetch(Request $request): JsonResponse
    {
        $type = $this->resolveType($request);
        $id = (int) $request['id'];

        if ($type === 'group') {
            $this->assertGroupMember($id, Auth::id());

            $query = Message::query()
                ->where('to_type', 'group')
                ->where('to_id', $id)
                ->latest();
        } else {
            $query = Message::query()
                ->where(function ($q) use ($id) {
                    $q->where('from_id', Auth::id())
                        ->where('to_id', $id)
                        ->where(function ($q) {
                            $q->whereNull('to_type')->orWhere('to_type', 'user');
                        });
                })
                ->orWhere(function ($q) use ($id) {
                    $q->where('from_id', $id)
                        ->where('to_id', Auth::id())
                        ->where(function ($q) {
                            $q->whereNull('to_type')->orWhere('to_type', 'user');
                        });
                })
                ->latest();
        }

        $messages = $query->paginate($request->per_page ?? $this->perPage);
        $totalMessages = $messages->total();
        $lastPage = $messages->lastPage();
        $response = [
            'total' => $totalMessages,
            'last_page' => $lastPage,
            'last_message_id' => collect($messages->items())->last()->id ?? null,
            'messages' => '',
        ];

        if ($totalMessages < 1) {
            $response['messages'] = "<p class=\"message-hint center-el\"><span>Say 'hi' and start messaging</span></p>";
            return Response::json($response);
        }
        if (count($messages->items()) < 1) {
            $response['messages'] = '';
            return Response::json($response);
        }

        $allMessages = null;
        $users = $type === 'group'
            ? User::query()->whereIn('id', collect($messages->items())->pluck('from_id')->unique())->pluck('name', 'id')->all()
            : [];

        foreach ($messages->reverse() as $message) {
            $data = Chatify::parseMessage($message);
            if ($type === 'group') {
                $data['room_type'] = 'group';
                $data['from_name'] = $users[$message->from_id] ?? null;
            }
            $allMessages .= Chatify::messageCard($data);
        }

        $response['messages'] = $allMessages;
        return Response::json($response);
    }

    public function seen(Request $request): JsonResponse
    {
        $type = $this->resolveType($request);
        $id = (int) $request['id'];

        if ($type === 'group') {
            $this->assertGroupMember($id, Auth::id());

            $messageIds = Message::query()
                ->where('to_type', 'group')
                ->where('to_id', $id)
                ->where('from_id', '!=', Auth::id())
                ->pluck('id');

            $rows = $messageIds->map(function ($messageId) {
                return [
                    'message_id' => $messageId,
                    'user_id' => Auth::id(),
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

            return Response::json(['status' => 1], 200);
        }

        Message::query()
            ->where('from_id', $id)
            ->where('to_id', Auth::id())
            ->where(function ($q) {
                $q->whereNull('to_type')->orWhere('to_type', 'user');
            })
            ->where('seen', 0)
            ->update(['seen' => 1]);

        return Response::json(['status' => 1], 200);
    }

    public function getContacts(Request $request): JsonResponse
    {
        $users = Message::join('users', function ($join) {
                $join->on('ch_messages.from_id', '=', 'users.id')
                    ->orOn('ch_messages.to_id', '=', 'users.id');
            })
            ->where(function ($q) {
                $q->where('ch_messages.from_id', Auth::id())
                    ->orWhere('ch_messages.to_id', Auth::id());
            })
            ->where(function ($q) {
                $q->whereNull('ch_messages.to_type')->orWhere('ch_messages.to_type', 'user');
            })
            ->where('users.id', '!=', Auth::id())
            ->select('users.*', DB::raw('MAX(ch_messages.created_at) max_created_at'))
            ->orderBy('max_created_at', 'desc')
            ->groupBy('users.id')
            ->paginate($request->per_page ?? $this->perPage);

        $contacts = '';
        if ($request->page < 2) {
            $contacts .= $this->renderGroupContacts();
        }

        if (count($users->items()) > 0) {
            foreach ($users->items() as $user) {
                $contacts .= Chatify::getContactItem($user);
            }
        } elseif ($request->page < 2 && trim($contacts) === '') {
            $contacts = '<p class="message-hint center-el"><span>Your contact list is empty</span></p>';
        }

        return Response::json([
            'contacts' => $contacts,
            'total' => $users->total() ?? 0,
            'last_page' => $users->lastPage() ?? 1,
        ], 200);
    }

    public function updateContactItem(Request $request): JsonResponse
    {
        $type = $this->resolveType($request);
        $id = (int) ($request['id'] ?? $request['user_id']);

        if ($type === 'group') {
            $group = ChGroup::query()->find($id);
            if (! $group) {
                return Response::json(['message' => 'Group not found!'], 404);
            }
            $contactItem = $this->renderGroupContactItem($group);

            return Response::json(['contactItem' => $contactItem], 200);
        }

        $user = User::where('id', $id)->first();
        if (! $user) {
            return Response::json(['message' => 'User not found!'], 401);
        }
        $contactItem = Chatify::getContactItem($user);

        return Response::json([
            'contactItem' => $contactItem,
        ], 200);
    }

    public function favorite(Request $request): JsonResponse
    {
        $userId = $request['user_id'];
        $favoriteStatus = Chatify::inFavorite($userId) ? 0 : 1;
        Chatify::makeInFavorite($userId, $favoriteStatus);

        return Response::json([
            'status' => @$favoriteStatus,
        ], 200);
    }

    public function getFavorites(Request $request): JsonResponse
    {
        $favoritesList = null;
        $favorites = \App\Models\ChFavorite::where('user_id', Auth::id());
        foreach ($favorites->get() as $favorite) {
            $user = User::where('id', $favorite->favorite_id)->first();
            $favoritesList .= view('Chatify::layouts.favorite', [
                'user' => $user,
            ]);
        }

        return Response::json([
            'count' => $favorites->count(),
            'favorites' => $favorites->count() > 0 ? $favoritesList : 0,
        ], 200);
    }

    public function search(Request $request): JsonResponse
    {
        $getRecords = null;
        $input = trim(filter_var($request['input']));

        $records = User::where('id', '!=', Auth::id())
            ->where('name', 'LIKE', "%{$input}%")
            ->paginate($request->per_page ?? $this->perPage);

        foreach ($records->items() as $record) {
            $getRecords .= view('Chatify::layouts.listItem', [
                'get' => 'search_item',
                'user' => Chatify::getUserWithAvatar($record),
            ])->render();
        }

        if ($records->total() < 1) {
            $getRecords = '<p class="message-hint center-el"><span>Nothing to show.</span></p>';
        }

        return Response::json([
            'records' => $getRecords,
            'total' => $records->total(),
            'last_page' => $records->lastPage(),
        ], 200);
    }

    public function sharedPhotos(Request $request): JsonResponse
    {
        $type = $this->resolveType($request);
        $id = (int) ($request['user_id'] ?? $request['id']);

        if ($type === 'group') {
            $shared = Message::query()
                ->where('to_type', 'group')
                ->where('to_id', $id)
                ->whereNotNull('attachment')
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($msg) {
                    $attachment = json_decode($msg->attachment);
                    if (! $attachment) {
                        return null;
                    }
                    $ext = pathinfo($attachment->new_name, PATHINFO_EXTENSION);
                    return in_array($ext, Chatify::getAllowedImages()) ? $attachment->new_name : null;
                })
                ->filter()
                ->values()
                ->all();
        } else {
            $shared = Chatify::getSharedPhotos($id);
        }

        $sharedPhotos = null;
        foreach ($shared as $file) {
            $sharedPhotos .= view('Chatify::layouts.listItem', [
                'get' => 'sharedPhoto',
                'image' => Chatify::getAttachmentUrl($file),
            ])->render();
        }

        return Response::json([
            'shared' => count($shared) > 0
                ? $sharedPhotos
                : '<p class="message-hint"><span>Nothing shared yet</span></p>',
        ], 200);
    }

    public function deleteConversation(Request $request): JsonResponse
    {
        $type = $this->resolveType($request);
        if ($type === 'group') {
            return Response::json(['deleted' => 0], 200);
        }

        $delete = Chatify::deleteConversation($request['id']);

        return Response::json([
            'deleted' => $delete ? 1 : 0,
        ], 200);
    }

    public function deleteMessage(Request $request): JsonResponse
    {
        $delete = Chatify::deleteMessage($request['id']);

        return Response::json([
            'deleted' => $delete ? 1 : 0,
        ], 200);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        return app(\Chatify\Http\Controllers\MessagesController::class)->updateSettings($request);
    }

    public function setActiveStatus(Request $request): JsonResponse
    {
        return app(\Chatify\Http\Controllers\MessagesController::class)->setActiveStatus($request);
    }

    public function createGroup(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer'],
        ]);

        $group = ChGroup::create([
            'name' => $request->input('name'),
            'created_by' => Auth::id(),
        ]);

        $memberIds = collect($request->input('member_ids'))->map(fn ($id) => (int) $id);
        $memberIds->push(Auth::id());
        $memberIds = $memberIds->unique();

        $rows = $memberIds->map(function ($id) use ($group) {
            return [
                'group_id' => $group->id,
                'user_id' => $id,
                'role' => $id === Auth::id() ? 'admin' : 'member',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->all();

        DB::table('ch_group_user')->insert($rows);

        $contactItem = $this->renderGroupContactItem($group);

        return Response::json([
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
            ],
            'contactItem' => $contactItem,
        ], 200);
    }

    private function renderGroupContacts(): string
    {
        $groups = DB::table('ch_groups')
            ->join('ch_group_user', 'ch_groups.id', '=', 'ch_group_user.group_id')
            ->where('ch_group_user.user_id', Auth::id())
            ->select('ch_groups.*')
            ->orderBy('ch_groups.name')
            ->get();

        $contacts = '';
        foreach ($groups as $group) {
            $contacts .= $this->renderGroupContactItem($group);
        }

        return $contacts;
    }

    private function renderGroupContactItem($group): string
    {
        $lastMessage = Message::query()
            ->where('to_type', 'group')
            ->where('to_id', $group->id)
            ->latest()
            ->first();

        $lastSenderName = null;
        if ($lastMessage) {
            $lastSenderName = User::query()
                ->where('id', $lastMessage->from_id)
                ->value('name');
        }

        $unseenCounter = $this->countUnreadGroupMessages($group->id, Auth::id());

        return view('Chatify::layouts.listItem', [
            'get' => 'group',
            'group' => $group,
            'lastMessage' => $lastMessage,
            'lastMessageSender' => $lastSenderName,
            'unseenCounter' => $unseenCounter,
        ])->render();
    }

    private function countUnreadGroupMessages(int $groupId, int $userId): int
    {
        return (int) DB::table('ch_messages')
            ->leftJoin('ch_message_reads', function ($join) use ($userId) {
                $join->on('ch_messages.id', '=', 'ch_message_reads.message_id')
                    ->where('ch_message_reads.user_id', $userId);
            })
            ->where('ch_messages.to_type', 'group')
            ->where('ch_messages.to_id', $groupId)
            ->where('ch_messages.from_id', '!=', $userId)
            ->whereNull('ch_message_reads.read_at')
            ->count();
    }

    private function resolveType(Request $request): string
    {
        $type = $request->input('type');
        if ($type === 'group') {
            return 'group';
        }
        if ($request->route()?->getName() === 'group') {
            return 'group';
        }
        return 'user';
    }

    private function assertGroupMember(int $groupId, int $userId): void
    {
        $exists = DB::table('ch_group_user')
            ->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->exists();

        abort_if(! $exists, 403, 'Not a group member');
    }

    private function groupAvatar(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="160" height="160" viewBox="0 0 160 160"><rect width="160" height="160" rx="80" fill="#4b4b4b"/><text x="50%" y="54%" text-anchor="middle" font-family="Arial, sans-serif" font-size="64" fill="#ffffff">G</text></svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
