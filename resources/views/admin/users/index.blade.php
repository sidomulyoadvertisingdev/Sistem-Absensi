@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="container-fluid user-management-modern">

    @php
        $totalUsers = $users->count();
        $totalAdmin = $users->filter(fn ($u) => $u->isAdmin())->count();
        $totalKaryawan = $users->filter(fn ($u) => $u->isKaryawan())->count();
        $totalUserBiasa = $users->filter(fn ($u) => $u->isUser())->count();
    @endphp

    {{-- HERO --}}
    <div class="card user-hero border-0 shadow-sm mb-4">
        <div class="card-body text-white d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h3 class="mb-1 font-weight-bold">Manajemen User</h3>
                <small>Kelola role user, promosi ke karyawan, dan penurunan role.</small>
            </div>
            <div class="hero-icon mt-2 mt-md-0">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </div>

    {{-- ALERT --}}
    @foreach (['success', 'warning', 'error'] as $msg)
        @if (session($msg))
            <div class="alert alert-{{ $msg == 'error' ? 'danger' : $msg }} user-alert">
                {{ session($msg) }}
            </div>
        @endif
    @endforeach

    {{-- RINGKASAN --}}
    <div class="row mb-3">
        <div class="col-lg-3 col-6">
            <div class="small-box um-box um-box-total">
                <div class="inner">
                    <h3>{{ $totalUsers }}</h3>
                    <p>Total User</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box um-box um-box-admin">
                <div class="inner">
                    <h3>{{ $totalAdmin }}</h3>
                    <p>Admin</p>
                </div>
                <div class="icon"><i class="fas fa-user-lock"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box um-box um-box-karyawan">
                <div class="inner">
                    <h3>{{ $totalKaryawan }}</h3>
                    <p>Karyawan</p>
                </div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box um-box um-box-user">
                <div class="inner">
                    <h3>{{ $totalUserBiasa }}</h3>
                    <p>User Biasa</p>
                </div>
                <div class="icon"><i class="fas fa-user"></i></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pb-0">
            <h5 class="mb-1 font-weight-bold">Daftar User</h5>
            <small class="text-muted">Promote atau demote role langsung dari tabel.</small>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover mb-0 user-table">
                <thead class="thead-light">
                    <tr>
                        <th width="6%">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>NIK</th>
                        <th width="14%">Role</th>
                        <th width="32%">Aksi</th>
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
                                <span class="badge um-role-badge
                                    @if($user->isAdmin()) um-role-admin
                                    @elseif($user->isKaryawan()) um-role-karyawan
                                    @else um-role-user
                                    @endif">
                                    {{ strtoupper($user->role) }}
                                </span>
                            </td>
                            <td>

                                {{-- PROMOTE --}}
                                @if($user->isUser())
                                    <form action="{{ route('admin.users.promote', $user) }}"
                                          method="POST"
                                          class="d-inline-block mb-1"
                                          onsubmit="return confirm('Jadikan user ini sebagai karyawan?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success um-action-btn">
                                            <i class="fas fa-arrow-up"></i>
                                            Promote -> Karyawan
                                        </button>
                                    </form>
                                @endif

                                {{-- DEMOTE --}}
                                @if($user->isKaryawan())
                                    <form action="{{ route('admin.users.demote', $user) }}"
                                          method="POST"
                                          class="d-inline-block mb-1"
                                          onsubmit="return confirm('Yakin turunkan jadi user biasa?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning um-action-btn">
                                            <i class="fas fa-arrow-down"></i>
                                            Demote -> User
                                        </button>
                                    </form>
                                @endif

                                {{-- ADMIN --}}
                                @if($user->isAdmin())
                                    <span class="text-muted font-weight-semibold">
                                        <i class="fas fa-lock"></i> Admin
                                    </span>
                                @endif

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
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

@push('styles')
<style>
.user-management-modern .user-hero {
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(120deg, #1d4f91 0%, #1e7b7f 100%);
    position: relative;
}

.user-management-modern .user-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 88% 15%, rgba(255, 255, 255, .18), transparent 45%);
}

.user-management-modern .user-hero .card-body {
    position: relative;
    z-index: 1;
}

.user-management-modern .hero-icon i {
    font-size: 40px;
    opacity: .9;
}

.user-management-modern .user-alert {
    border: 1px solid transparent;
}

.user-management-modern .um-box {
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 0;
    color: #fff;
    box-shadow: 0 10px 20px rgba(16, 39, 74, .10);
}

.user-management-modern .um-box .inner {
    padding: 18px;
}

.user-management-modern .um-box h3 {
    font-weight: 700;
}

.user-management-modern .um-box .icon {
    top: 10px;
    right: 12px;
    font-size: 48px;
    opacity: .22;
}

.user-management-modern .um-box-total {
    background: linear-gradient(135deg, #2f6fb3 0%, #225b94 100%);
}

.user-management-modern .um-box-admin {
    background: linear-gradient(135deg, #cd5f3d 0%, #a84b2e 100%);
}

.user-management-modern .um-box-karyawan {
    background: linear-gradient(135deg, #2f8f5f 0%, #23734b 100%);
}

.user-management-modern .um-box-user {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
}

.user-management-modern .card {
    border-radius: 14px;
}

.user-management-modern .user-table thead.thead-light th {
    background: #f3f7fd;
    color: #334b73;
    border-bottom: 1px solid #d9e3f1;
}

.user-management-modern .user-table tbody tr:hover {
    background: #f9fbff;
}

.user-management-modern .um-role-badge {
    border-radius: 999px;
    padding: .4rem .65rem;
    font-size: .72rem;
    letter-spacing: .4px;
}

.user-management-modern .um-role-admin {
    background: #ffe3dc;
    color: #a8442a;
}

.user-management-modern .um-role-karyawan {
    background: #ddf7e8;
    color: #1f7249;
}

.user-management-modern .um-role-user {
    background: #e8edf5;
    color: #42516d;
}

.user-management-modern .um-action-btn {
    border-radius: 10px;
    font-weight: 600;
    padding: .35rem .6rem;
}

:root[data-theme='dark'] .user-management-modern .user-table thead.thead-light th {
    background: #1b2738;
    color: #d9e4f6;
    border-bottom-color: #2f3f57;
}

:root[data-theme='dark'] .user-management-modern .user-table tbody tr:hover {
    background: #1f2d40;
}

:root[data-theme='dark'] .user-management-modern .um-role-admin {
    background: #4f3131;
    color: #ffd6cd;
}

:root[data-theme='dark'] .user-management-modern .um-role-karyawan {
    background: #284535;
    color: #d3f5df;
}

:root[data-theme='dark'] .user-management-modern .um-role-user {
    background: #2d3a4d;
    color: #d2deee;
}
</style>
@endpush
