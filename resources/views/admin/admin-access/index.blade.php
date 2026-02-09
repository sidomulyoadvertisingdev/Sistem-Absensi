@extends('layouts.app')

@section('title', 'Akses Admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1 font-weight-bold">Akses Admin</h4>
            <small class="text-muted">Kelola akun admin dan hak akses menu aplikasi.</small>
        </div>
        <a href="{{ route('admin.admin-access.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus mr-1"></i> Tambah Admin
        </a>
    </div>

    @foreach (['success', 'warning', 'error'] as $msg)
        @if (session($msg))
            <div class="alert alert-{{ $msg === 'error' ? 'danger' : $msg }}">
                {{ session($msg) }}
            </div>
        @endif
    @endforeach

    <div class="card border-0 shadow-sm">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:70px;">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th style="width:180px;">Role</th>
                        <th style="width:190px;">Hak Akses</th>
                        <th style="width:230px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                        @php
                            $resolvedPermissions = $admin->resolvedAdminPermissions();
                            $roleLabel = $roleOptions[$admin->role] ?? strtoupper($admin->role);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                <span class="badge badge-info">{{ $roleLabel }}</span>
                            </td>
                            <td>
                                @if($admin->isOwner())
                                    <span class="badge badge-success">Semua Akses</span>
                                @else
                                    <span class="badge badge-secondary">
                                        {{ count($resolvedPermissions) }} menu
                                    </span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.admin-access.edit', $admin->id) }}"
                                    class="btn btn-sm btn-warning">
                                    <i class="fas fa-key mr-1"></i> Hak Akses
                                </a>

                                @if(!$admin->isOwner() && auth()->id() !== $admin->id)
                                    <form action="{{ route('admin.admin-access.destroy', $admin->id) }}"
                                        method="POST"
                                        class="d-inline-block"
                                        onsubmit="return confirm('Hapus akun admin ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Belum ada akun admin
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

