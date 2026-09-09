@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="container-fluid user-management-modern">

    @php
        $totalUsers = $users->count();
        $totalAdmin = $users->filter(fn ($u) => $u->isAdmin())->count();
        $totalKaryawan = $users->filter(fn ($u) => $u->isKaryawan())->count();
        $totalUserBiasa = $users->filter(fn ($u) => $u->isUser())->count();
        $labelPeran = [
            'owner' => 'Pemilik',
            'admin' => 'Admin',
            'karyawan' => 'Karyawan',
            'user' => 'Pengguna',
        ];
    @endphp

    <div class="panel-halaman">
        <div>
            <span class="label-halaman">Manajemen Akses</span>
            <h1>Manajemen Pengguna</h1>
            <p>Kelola peran pengguna, promosi karyawan, dan perubahan akses akun dari satu tempat.</p>
        </div>
        <div class="ikon-halaman">
            <i class="fas fa-user-shield"></i>
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

    <div class="grid-ringkasan">
        <div class="kartu-ringkasan aksen-biru">
            <span class="ikon-ringkasan"><i class="fas fa-users"></i></span>
            <span>Total Pengguna</span>
            <strong>{{ $totalUsers }}</strong>
            <small>Semua akun terdaftar</small>
        </div>
        <div class="kartu-ringkasan aksen-merah">
            <span class="ikon-ringkasan"><i class="fas fa-user-lock"></i></span>
            <span>Admin</span>
            <strong>{{ $totalAdmin }}</strong>
            <small>Akun pengelola sistem</small>
        </div>
        <div class="kartu-ringkasan aksen-hijau">
            <span class="ikon-ringkasan"><i class="fas fa-user-tie"></i></span>
            <span>Karyawan</span>
            <strong>{{ $totalKaryawan }}</strong>
            <small>Akun karyawan aktif</small>
        </div>
        <div class="kartu-ringkasan aksen-abu">
            <span class="ikon-ringkasan"><i class="fas fa-user"></i></span>
            <span>Pengguna Biasa</span>
            <strong>{{ $totalUserBiasa }}</strong>
            <small>Akun belum menjadi karyawan</small>
        </div>
    </div>

    <section class="panel-data">
        <div class="panel-judul">
            <div>
                <h2>Daftar Pengguna</h2>
                <p>Promosi atau turunkan peran langsung dari tabel.</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 user-table">
                <thead>
                    <tr>
                        <th width="6%">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>NIK</th>
                        <th width="14%">Peran</th>
                        <th width="32%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="identitas-pengguna">
                                    <span class="avatar-pengguna">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    <strong>{{ $user->name }}</strong>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->nik ?? '-' }}</td>
                            <td>
                                <span class="badge um-role-badge
                                    @if($user->isAdmin()) um-role-admin
                                    @elseif($user->isKaryawan()) um-role-karyawan
                                    @else um-role-user
                                    @endif">
                                    {{ $labelPeran[$user->role] ?? ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <div class="aksi-pengguna">

                                    {{-- PROMOTE --}}
                                    @if($user->isUser())
                                        <form action="{{ route('admin.users.promote', $user) }}"
                                              method="POST"
                                              onsubmit="return confirm('Jadikan pengguna ini sebagai karyawan?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success um-action-btn">
                                                <i class="fas fa-arrow-up"></i>
                                                Jadikan Karyawan
                                            </button>
                                        </form>
                                    @endif

                                    {{-- DEMOTE --}}
                                    @if($user->isKaryawan())
                                        <form action="{{ route('admin.users.demote', $user) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin turunkan jadi pengguna biasa?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning um-action-btn">
                                                <i class="fas fa-arrow-down"></i>
                                                Jadikan Pengguna
                                            </button>
                                        </form>
                                    @endif

                                    {{-- ADMIN --}}
                                    @if($user->isAdmin())
                                        <span class="status-terkunci">
                                            <i class="fas fa-lock"></i> Admin
                                        </span>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tidak ada pengguna
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection

@push('styles')
<style>
.user-management-modern {
    --panel-border: #e4e9f1;
    --panel-shadow: 0 14px 34px rgba(22, 34, 51, .08);
    --soft-blue: #edf4ff;
    --soft-green: #e8f8ef;
    --soft-red: #fff0eb;
    --soft-gray: #f1f5f9;
}

.user-management-modern .panel-halaman {
    align-items: center;
    background: var(--surface);
    border: 1px solid var(--panel-border);
    border-radius: 16px;
    box-shadow: var(--panel-shadow);
    display: flex;
    justify-content: space-between;
    margin-bottom: 22px;
    padding: 24px;
}

.user-management-modern .label-halaman {
    color: var(--accent-strong);
    display: block;
    font-size: .78rem;
    font-weight: 800;
    letter-spacing: 0;
    margin-bottom: 8px;
}

.user-management-modern .panel-halaman h1 {
    color: var(--text);
    font-size: 1.55rem;
    font-weight: 800;
    margin: 0 0 6px;
}

.user-management-modern .panel-halaman p {
    color: var(--text-muted);
    font-size: .95rem;
    margin: 0;
}

.user-management-modern .ikon-halaman {
    align-items: center;
    background: linear-gradient(135deg, #edf4ff, #e8f8ef);
    border: 1px solid #dce7f8;
    border-radius: 14px;
    color: var(--accent-strong);
    display: inline-flex;
    font-size: 1.55rem;
    height: 58px;
    justify-content: center;
    width: 58px;
}

.user-management-modern .user-alert {
    border: 1px solid transparent;
    border-radius: 12px;
    box-shadow: none;
}

.user-management-modern .grid-ringkasan {
    display: grid;
    gap: 16px;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    margin-bottom: 22px;
}

.user-management-modern .kartu-ringkasan {
    background: var(--surface);
    border: 1px solid var(--panel-border);
    border-radius: 14px;
    box-shadow: var(--panel-shadow);
    min-height: 150px;
    padding: 18px;
}

.user-management-modern .ikon-ringkasan {
    align-items: center;
    border-radius: 12px;
    display: inline-flex;
    font-size: 1rem;
    height: 38px;
    justify-content: center;
    margin-bottom: 18px;
    width: 38px;
}

.user-management-modern .kartu-ringkasan span:not(.ikon-ringkasan) {
    color: var(--text);
    display: block;
    font-weight: 700;
    margin-bottom: 8px;
}

.user-management-modern .kartu-ringkasan strong {
    color: var(--text);
    display: block;
    font-size: 1.9rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 9px;
}

.user-management-modern .kartu-ringkasan small {
    color: var(--text-muted);
    font-weight: 600;
}

.user-management-modern .aksen-biru .ikon-ringkasan { background: var(--soft-blue); color: #315fce; }
.user-management-modern .aksen-merah .ikon-ringkasan { background: var(--soft-red); color: #bf563c; }
.user-management-modern .aksen-hijau .ikon-ringkasan { background: var(--soft-green); color: #24865d; }
.user-management-modern .aksen-abu .ikon-ringkasan { background: var(--soft-gray); color: #475569; }

.user-management-modern .panel-data {
    background: var(--surface);
    border: 1px solid var(--panel-border);
    border-radius: 16px;
    box-shadow: var(--panel-shadow);
    overflow: hidden;
}

.user-management-modern .panel-judul {
    align-items: center;
    border-bottom: 1px solid var(--panel-border);
    display: flex;
    justify-content: space-between;
    padding: 18px 22px;
}

.user-management-modern .panel-judul h2 {
    color: var(--text);
    font-size: 1.02rem;
    font-weight: 800;
    margin: 0 0 4px;
}

.user-management-modern .panel-judul p {
    color: var(--text-muted);
    margin: 0;
}

.user-management-modern .user-table thead th {
    background: var(--surface-2);
    border-bottom: 1px solid var(--panel-border);
    color: var(--text-muted);
    font-size: .78rem;
    font-weight: 800;
}

.user-management-modern .user-table tbody td {
    border-top: 1px solid var(--panel-border);
    color: var(--text);
}

.user-management-modern .user-table tbody tr:hover {
    background: color-mix(in srgb, var(--surface-2) 72%, transparent);
}

.user-management-modern .identitas-pengguna {
    align-items: center;
    display: flex;
    gap: 10px;
}

.user-management-modern .avatar-pengguna {
    align-items: center;
    background: var(--soft-blue);
    border: 1px solid #dce7f8;
    border-radius: 50%;
    color: var(--accent-strong);
    display: inline-flex;
    flex: 0 0 34px;
    font-size: .82rem;
    font-weight: 800;
    height: 34px;
    justify-content: center;
    width: 34px;
}

.user-management-modern .um-role-badge {
    border-radius: 999px;
    font-size: .74rem;
    font-weight: 800;
    padding: .42rem .68rem;
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

.user-management-modern .aksi-pengguna {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.user-management-modern .aksi-pengguna form {
    margin: 0;
}

.user-management-modern .um-action-btn {
    border-radius: 10px;
    font-weight: 700;
    padding: .42rem .7rem;
}

.user-management-modern .status-terkunci {
    align-items: center;
    color: var(--text-muted);
    display: inline-flex;
    font-weight: 700;
    gap: 7px;
}

:root[data-theme='dark'] .user-management-modern {
    --panel-border: #2c3b52;
    --panel-shadow: 0 14px 36px rgba(0, 0, 0, .28);
    --soft-blue: #1b315d;
    --soft-green: #1d3a30;
    --soft-red: #3b2928;
    --soft-gray: #263449;
}

:root[data-theme='dark'] .user-management-modern .ikon-halaman,
:root[data-theme='dark'] .user-management-modern .avatar-pengguna {
    border-color: #334464;
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

@media (max-width: 991.98px) {
    .user-management-modern .grid-ringkasan {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 575.98px) {
    .user-management-modern .panel-halaman {
        align-items: flex-start;
        flex-direction: column;
        gap: 16px;
    }

    .user-management-modern .grid-ringkasan {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
