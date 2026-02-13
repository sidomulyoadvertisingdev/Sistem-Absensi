@extends('layouts.app')

@section('title', 'Chat')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <strong>Buat Grup</strong>
                </div>
                <div class="card-body p-0">
                    <form method="POST" action="{{ route('admin.chat.group') }}">
                        @csrf
                        <div class="p-3">
                            <div class="form-group">
                                <label>Nama Grup</label>
                                <input type="text" name="name" class="form-control" placeholder="Mis. Tim Marketing" required>
                            </div>
                            <small class="text-muted d-block mb-2">Pilih member (checkbox):</small>
                        </div>
                        @include('admin.chat.partials.user-list', ['users' => $users])
                        <div class="p-3">
                            <button class="btn btn-primary btn-block" type="submit">
                                <i class="fas fa-plus-circle mr-1"></i> Buat Grup
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 mt-3">
                <div class="card-header">
                    <strong>Mulai Chat Langsung</strong>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.chat.direct') }}">
                        @csrf
                        <div class="form-group">
                            <label>Pilih Kontak</label>
                            <select name="user_id" class="form-control" required>
                                <option value="">-- pilih user --</option>
                                @foreach($users as $user)
                                    @if($user->id !== auth()->id())
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-outline-primary btn-block" type="submit">
                            <i class="fas fa-paper-plane mr-1"></i> Mulai Chat
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Daftar Room</strong>
                        <small class="text-muted d-block">Semua room yang Anda punya akses</small>
                    </div>
                    <span class="badge badge-primary">{{ $rooms->count() }} room</span>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($rooms as $room)
                        <div class="list-group-item d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center">
                                    <strong class="mr-2">{{ $room->name }}</strong>
                                    @if($room->is_group)
                                        <span class="badge badge-info">Group</span>
                                    @else
                                        <span class="badge badge-secondary">Direct</span>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $room->members->pluck('name')->join(', ') }}
                                </small>
                                <div class="text-muted" style="font-size: 0.85rem;">
                                    {{ $room->latestMessage?->text ? Str::limit($room->latestMessage->text, 60) : 'Belum ada pesan' }}
                                </div>
                            </div>
                            @php($unread = $room->pivot?->unread_count ?? 0)
                            @if($unread > 0)
                                <span class="badge badge-danger ml-3">{{ $unread }}</span>
                            @endif
                        </div>
                    @empty
                        <div class="list-group-item text-muted">Belum ada room.</div>
                    @endforelse
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <strong>Web Chat Client</strong>
                    <small class="text-muted d-block">Bundle JS front-end bisa di-mount ke elemen berikut:</small>
                </div>
                <div class="card-body p-0">
                    <div id="chat-panel-app" class="p-3" style="min-height: 480px;">
                        <div class="text-center text-muted my-5">
                            <i class="fas fa-bolt fa-2x mb-3 text-primary"></i>
                            <div class="h5 font-weight-bold">Siap dipakai</div>
                            <p class="mb-0">
                                Hubungkan ke REST <code>/api/chat/*</code> & Socket.IO auth Bearer.
                            </p>
                            <p class="mb-0">Elemen ini menunggu bundle React/Vue/Alpine.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
