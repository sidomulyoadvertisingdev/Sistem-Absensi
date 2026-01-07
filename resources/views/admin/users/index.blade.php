@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Manajemen User</h1>
    </div>

    {{-- ALERT --}}
    @foreach (['success','warning','error'] as $msg)
        @if (session($msg))
            <div class="alert alert-{{ $msg == 'error' ? 'danger' : $msg }}">
                {{ session($msg) }}
            </div>
        @endif
    @endforeach

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th width="5%">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>NIK</th>
                        <th>Role</th>
                        <th width="30%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->nik ?? '-' }}</td>
                            <td>
                                <span class="badge
                                    @if($user->isAdmin()) badge-danger
                                    @elseif($user->isKaryawan()) badge-success
                                    @else badge-secondary
                                    @endif
                                ">
                                    {{ strtoupper($user->role) }}
                                </span>
                            </td>
                            <td>

                                {{-- PROMOTE --}}
                                @if($user->isUser())
                                    <form action="{{ route('admin.users.promote', $user) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Jadikan user ini sebagai karyawan?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-arrow-up"></i>
                                            Promote → Karyawan
                                        </button>
                                    </form>
                                @endif

                                {{-- DEMOTE --}}
                                @if($user->isKaryawan())
                                    <form action="{{ route('admin.users.demote', $user) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Yakin turunkan jadi user biasa?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="fas fa-arrow-down"></i>
                                            Demote → User
                                        </button>
                                    </form>
                                @endif

                                {{-- ADMIN --}}
                                @if($user->isAdmin())
                                    <span class="text-muted">
                                        <i class="fas fa-lock"></i> Admin
                                    </span>
                                @endif

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Tidak ada user
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
