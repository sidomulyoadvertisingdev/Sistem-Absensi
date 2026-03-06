<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Admin Panel'))</title>

    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('theme_mode');
                var preferred = window.matchMedia('(prefers-color-scheme: dark)').matches
                    ? 'dark'
                    : 'light';
                var mode = savedTheme || preferred;
                document.documentElement.setAttribute('data-theme', mode);
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --accent: #1f5d96;
            --accent-soft: #e8f0fb;
            --accent-strong: #124978;
            --ok: #188754;
            --warning: #db8a13;
            --danger: #cd5f3d;
        }

        :root[data-theme='light'] {
            --bg: #eef2f7;
            --surface: #ffffff;
            --surface-2: #f8fafd;
            --text: #1e2a3d;
            --text-muted: #62718f;
            --border: #dbe4f1;
            --sidebar-bg: #132b45;
            --sidebar-text: #c2d4eb;
            --sidebar-hover: rgba(255, 255, 255, .09);
            --sidebar-active-bg: linear-gradient(120deg, #2b6ea8 0%, #236f73 100%);
            --sidebar-active-text: #ffffff;
            --navbar-bg: #ffffff;
            --shadow: 0 10px 24px rgba(24, 44, 79, .08);
        }

        :root[data-theme='dark'] {
            --bg: #101721;
            --surface: #1a2433;
            --surface-2: #202c3e;
            --text: #e2e9f5;
            --text-muted: #9eb0cc;
            --border: #2f3c51;
            --sidebar-bg: #0d1622;
            --sidebar-text: #a5b6d2;
            --sidebar-hover: rgba(255, 255, 255, .07);
            --sidebar-active-bg: linear-gradient(120deg, #355f8f 0%, #2f7b7f 100%);
            --sidebar-active-text: #ffffff;
            --navbar-bg: #1a2433;
            --shadow: 0 12px 28px rgba(0, 0, 0, .35);
        }

        body {
            background: var(--bg) !important;
            color: var(--text);
        }

        h1, h2, h3, h4, h5, h6 {
            color: var(--text);
        }

        .content-header h1,
        .content h1,
        .content h2,
        .content h3,
        .content h4,
        .content h5,
        .content h6 {
            color: var(--text);
            letter-spacing: .2px;
        }

        .content-wrapper {
            background: var(--bg) !important;
        }

        .main-header.navbar {
            background: var(--navbar-bg) !important;
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow);
            min-height: 62px;
        }

        .main-header .nav-link,
        .main-header .dropdown-toggle,
        .main-header .dropdown-item {
            color: var(--text) !important;
        }

        .dropdown-menu {
            background: var(--surface);
            border-color: var(--border);
            box-shadow: var(--shadow);
        }

        .main-sidebar.sidebar-dark-primary {
            background: var(--sidebar-bg) !important;
        }

        .main-sidebar .brand-link {
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            background: transparent !important;
            min-height: 72px;
            padding: .55rem .75rem;
            margin-bottom: .25rem;
        }

        .main-sidebar .sidebar {
            padding-top: .3rem;
        }

        .sidebar-brand-stack {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            text-decoration: none !important;
        }

        .sidebar-brand-stack img {
            width: auto;
            height: 34px;
            object-fit: contain;
        }

        .sidebar-brand-stack .app-version {
            font-size: .68rem;
            font-weight: 700;
            color: rgba(255, 255, 255, .72);
            letter-spacing: .25px;
        }

        .navbar-noti-btn {
            position: relative;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            border-radius: 10px;
            width: 38px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: .2s ease;
            text-decoration: none !important;
        }

        .navbar-noti-btn:hover {
            background: var(--surface-2);
            color: var(--accent-strong);
        }

        .navbar-noti-btn .badge {
            position: absolute;
            top: -7px;
            right: -7px;
            font-size: .62rem;
            min-width: 18px;
            text-align: center;
            padding: .18rem .35rem;
            border-radius: 999px;
        }

        .dropdown-menu-notif {
            width: 330px;
            max-width: 92vw;
            padding: 0;
            overflow: hidden;
            border-radius: 12px;
        }

        .dropdown-menu-notif .dropdown-header {
            font-weight: 700;
            color: var(--text);
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
            padding: .7rem .95rem;
        }

        .dropdown-menu-notif .dropdown-item {
            white-space: normal;
            border-bottom: 1px solid var(--border);
            padding: .65rem .95rem;
            line-height: 1.25;
        }

        .dropdown-menu-notif .dropdown-item:last-child {
            border-bottom: 0;
        }

        .dropdown-menu-notif .notif-title {
            font-weight: 700;
            color: var(--text);
        }

        .dropdown-menu-notif .notif-meta {
            color: var(--text-muted);
            font-size: .78rem;
        }

        .dropdown-menu-notif .dropdown-footer {
            display: block;
            text-align: center;
            padding: .65rem .95rem;
            font-weight: 600;
            background: var(--surface-2);
            border-top: 1px solid var(--border);
            color: var(--accent-strong) !important;
        }

        .main-sidebar .nav-link {
            color: var(--sidebar-text) !important;
            border-radius: 10px;
            margin: 3px 10px;
        }

        .main-sidebar .nav-sidebar > .nav-item:first-child {
            margin-top: .35rem;
        }

        .main-sidebar .nav-link:hover {
            background: var(--sidebar-hover) !important;
            color: #fff !important;
        }

        .main-sidebar .nav-link.active {
            background: var(--sidebar-active-bg) !important;
            color: var(--sidebar-active-text) !important;
            box-shadow: 0 6px 14px rgba(24, 52, 86, .22);
        }

        .card,
        .small-box,
        .info-box,
        .modal-content {
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }

        .card-header,
        .card-footer {
            background: var(--surface-2);
            border-color: var(--border);
        }

        .table,
        .table td,
        .table th,
        .table thead th {
            color: var(--text);
            border-color: var(--border);
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background: color-mix(in srgb, var(--surface) 85%, var(--surface-2));
        }

        .text-muted,
        .small,
        small {
            color: var(--text-muted) !important;
        }

        .form-control,
        .custom-select {
            background: var(--surface);
            border-color: var(--border);
            color: var(--text);
        }

        .form-control:focus,
        .custom-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 .2rem rgba(31, 93, 150, .2);
        }

        .theme-toggle-btn {
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text);
            border-radius: 10px;
            width: 38px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: .2s ease;
        }

        .theme-toggle-btn:hover {
            background: var(--surface-2);
            color: var(--accent-strong);
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            border-width: 1px;
            box-shadow: none !important;
        }

        .btn-primary {
            background: linear-gradient(120deg, #1d4f91 0%, #1e7b7f 100%);
            border-color: transparent;
            color: #fff;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background: linear-gradient(120deg, #19477f 0%, #1a6f72 100%);
            color: #fff;
        }

        .btn-secondary {
            background: #6b7a91;
            border-color: #6b7a91;
            color: #fff;
        }

        .btn-success {
            background: #238058;
            border-color: #238058;
        }

        .btn-warning {
            background: #d08a1e;
            border-color: #d08a1e;
            color: #fff;
        }

        .btn-danger {
            background: #bd5a3b;
            border-color: #bd5a3b;
        }

        .btn-light {
            background: var(--surface-2);
            border-color: var(--border);
            color: var(--text);
        }

        .alert {
            border-radius: 12px;
            border-width: 1px;
            border-style: solid;
            box-shadow: var(--shadow);
        }

        .alert-success {
            background: #e8f7ef;
            color: #1f6f4c;
            border-color: #bfe8cf;
        }

        .alert-warning {
            background: #fff5e6;
            color: #8a5914;
            border-color: #f5ddb0;
        }

        .alert-danger {
            background: #fdebea;
            color: #8d3c2b;
            border-color: #f1c4bc;
        }

        .badge {
            border-radius: 999px;
            font-size: .72rem;
            letter-spacing: .35px;
            padding: .38em .62em;
            font-weight: 600;
        }

        .badge-primary { background: #345f95; color: #fff; }
        .badge-info { background: #2f6fb3; color: #fff; }
        .badge-success { background: #2f8f5f; color: #fff; }
        .badge-warning { background: #e09a2d; color: #fff; }
        .badge-danger { background: #cd5f3d; color: #fff; }
        .badge-secondary { background: #718097; color: #fff; }
        .badge-dark { background: #374558; color: #fff; }

        .table thead.thead-dark th {
            background: #1d4f91;
            border-color: #315f98;
            color: #fff;
        }

        .table thead.thead-light th {
            background: #f3f7fd;
            color: #334b73;
            border-bottom: 1px solid #d9e3f1;
        }

        .table-hover tbody tr:hover {
            background: #f9fbff;
        }

        .table-bordered td,
        .table-bordered th {
            border-color: var(--border);
        }

        .input-group-text {
            background: var(--surface-2);
            border-color: var(--border);
            color: var(--text);
        }

        .modal-header,
        .modal-footer {
            background: var(--surface-2);
            border-color: var(--border);
        }

        .pagination .page-link {
            color: var(--text);
            background: var(--surface);
            border-color: var(--border);
        }

        .pagination .page-item.active .page-link {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .small-box {
            border-radius: 14px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .small-box .small-box-footer {
            background: rgba(255, 255, 255, .16);
            color: rgba(255, 255, 255, .95);
        }

        .small-box .small-box-footer:hover {
            background: rgba(255, 255, 255, .24);
            color: #fff;
        }

        .small-box.bg-primary { background: linear-gradient(135deg, #3d66a4 0%, #30558b 100%); }
        .small-box.bg-info { background: linear-gradient(135deg, #2f6fb3 0%, #23588f 100%); }
        .small-box.bg-success { background: linear-gradient(135deg, #2f8f5f 0%, #23734b 100%); }
        .small-box.bg-warning { background: linear-gradient(135deg, #d7952b 0%, #b6781a 100%); }
        .small-box.bg-danger { background: linear-gradient(135deg, #cd5f3d 0%, #af4d31 100%); }
        .small-box.bg-secondary { background: linear-gradient(135deg, #6b7a91 0%, #56657d 100%); }
        .small-box.bg-dark { background: linear-gradient(135deg, #334155 0%, #1f2b3d 100%); }
        .small-box.bg-teal { background: linear-gradient(135deg, #217f86 0%, #16666c 100%); }

        .small-box[class*='bg-'],
        .small-box[class*='bg-'] p,
        .small-box[class*='bg-'] h3 {
            color: #fff !important;
        }

        :root[data-theme='dark'] .btn-light {
            background: #243144;
            border-color: #334760;
            color: #dce6f7;
        }

        :root[data-theme='dark'] .alert-success {
            background: #1f3a2e;
            color: #cdeeda;
            border-color: #2c5946;
        }

        :root[data-theme='dark'] .alert-warning {
            background: #3d3021;
            color: #f2d9aa;
            border-color: #5a452f;
        }

        :root[data-theme='dark'] .alert-danger {
            background: #3d2628;
            color: #f4cccc;
            border-color: #5e3a3f;
        }

        :root[data-theme='dark'] .table thead.thead-dark th {
            background: #2a4368;
            border-color: #395375;
        }

        :root[data-theme='dark'] .table thead.thead-light th {
            background: #1b2738;
            color: #d9e4f6;
            border-bottom-color: #2f3f57;
        }

        :root[data-theme='dark'] .table-hover tbody tr:hover {
            background: #1f2d40;
        }

        :root[data-theme='dark'] .dropdown-divider {
            border-top-color: var(--border);
        }

        @media (max-width: 575.98px) {
            .main-header.navbar {
                min-height: 56px;
            }
        }
    </style>
    @stack('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">

    <div class="wrapper">

        {{-- ================= NAVBAR ================= --}}
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                @auth
                    @php
                        $panelUser = auth()->user();
                        $pendingSubmissionCount = 0;
                        $pendingLemburCount = 0;
                        $notifikasiItems = collect();

                        $canSubmissionNotif = $panelUser->hasAdminPermission('submission');
                        $canLemburNotif = $panelUser->hasAdminPermission('lembur');

                        if ($panelUser->isPanelAdmin() && $canSubmissionNotif) {
                            $pendingSubmissionCount = \App\Models\Submission::where('status', 'pending')->count();

                            $submissionItems = \App\Models\Submission::with('user:id,name')
                                ->where('status', 'pending')
                                ->latest('created_at')
                                ->limit(4)
                                ->get()
                                ->map(function ($item) {
                                    return [
                                        'title' => 'Submission Baru',
                                        'meta' => ($item->user->name ?? 'Karyawan') . ' mengirim pengajuan',
                                        'time' => optional($item->created_at)->diffForHumans(),
                                        'url' => route('admin.submission.index'),
                                        'created_at' => $item->created_at,
                                    ];
                                });
                        } else {
                            $submissionItems = collect();
                        }

                        if ($panelUser->isPanelAdmin() && $canLemburNotif) {
                            $pendingLemburCount = \App\Models\Lembur::whereIn('status', ['pending', 'requested'])->count();

                            $lemburItems = \App\Models\Lembur::with('user:id,name')
                                ->whereIn('status', ['pending', 'requested'])
                                ->latest('created_at')
                                ->limit(4)
                                ->get()
                                ->map(function ($item) {
                                    return [
                                        'title' => 'Pengajuan Lembur',
                                        'meta' => ($item->user->name ?? 'Karyawan') . ' mengajukan lembur',
                                        'time' => optional($item->created_at)->diffForHumans(),
                                        'url' => route('admin.lembur'),
                                        'created_at' => $item->created_at,
                                    ];
                                });
                        } else {
                            $lemburItems = collect();
                        }

                        $notifikasiItems = $submissionItems
                            ->concat($lemburItems)
                            ->sortByDesc('created_at')
                            ->take(6)
                            ->values();

                        $totalNotifikasi = $pendingSubmissionCount + $pendingLemburCount;
                    @endphp
                @endauth

                @auth
                    @if($panelUser->hasAdminPermission('chat'))
                        <li class="nav-item mr-2 d-flex align-items-center">
                            <a class="navbar-noti-btn"
                                href="{{ route('chatify') }}"
                                title="Chat">
                                <i class="fas fa-comments"></i>
                            </a>
                        </li>
                    @endif
                    @if($panelUser->isPanelAdmin() && ($canSubmissionNotif || $canLemburNotif))
                        <li class="nav-item dropdown mr-2 d-flex align-items-center">
                            <a class="navbar-noti-btn"
                                href="#"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                                title="Notifikasi">
                                <i class="fas fa-bell"></i>
                                @if($totalNotifikasi > 0)
                                    <span class="badge badge-danger">
                                        {{ $totalNotifikasi > 99 ? '99+' : $totalNotifikasi }}
                                    </span>
                                @endif
                            </a>

                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-notif">
                                <div class="dropdown-header">
                                    Notifikasi
                                    @if($totalNotifikasi > 0)
                                        <span class="badge badge-danger ml-1">{{ $totalNotifikasi }}</span>
                                    @endif
                                </div>

                                @forelse($notifikasiItems as $notif)
                                    <a href="{{ $notif['url'] }}" class="dropdown-item">
                                        <div class="notif-title">{{ $notif['title'] }}</div>
                                        <div class="notif-meta">{{ $notif['meta'] }}</div>
                                        <div class="notif-meta">{{ $notif['time'] ?? '-' }}</div>
                                    </a>
                                @empty
                                    <div class="dropdown-item text-muted">
                                        Belum ada notifikasi baru
                                    </div>
                                @endforelse

                                <a href="{{ route('admin.submission.index') }}" class="dropdown-footer">
                                    Lihat semua pengajuan
                                </a>
                            </div>
                        </li>
                    @endif
                @endauth

                <li class="nav-item mr-2 d-flex align-items-center">
                    <button type="button"
                        class="theme-toggle-btn"
                        id="themeToggleBtn"
                        title="Aktifkan mode malam"
                        aria-label="Aktifkan mode malam">
                        <i id="themeToggleIcon" class="fas fa-moon"></i>
                    </button>
                </li>
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                            {{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ route('logout') }}"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </div>
                    </li>
                @endauth
            </ul>
        </nav>

        {{-- ================= SIDEBAR ================= --}}
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('admin.dashboard') }}" class="brand-link text-center sidebar-brand-stack">
                <img src="{{ asset('images/logosm.svg') }}" alt="Logo SM">
                <span class="app-version">v{{ config('app.app_version', '1.0.0') }}</span>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        @php($menuUser = Auth::user())

                        {{-- Dashboard --}}
                        @if($menuUser->hasAdminPermission('dashboard'))
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}"
                                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-home"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>
                        @endif

                        {{-- Manajemen User --}}
                        @if($menuUser->hasAdminPermission('users') || $menuUser->hasAdminPermission('manage_admin_access'))
                            <li class="nav-item has-treeview {{ request()->is('admin/users*') || request()->is('admin/admin-access*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ request()->is('admin/users*') || request()->is('admin/admin-access*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-cog"></i>
                                    <p>Manajemen User<i class="right fas fa-angle-left"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if($menuUser->hasAdminPermission('users'))
                                        <li class="nav-item">
                                            <a href="{{ route('admin.users.index') }}"
                                                class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Daftar User</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if($menuUser->hasAdminPermission('manage_admin_access'))
                                        <li class="nav-item">
                                            <a href="{{ route('admin.admin-access.index') }}"
                                                class="nav-link {{ request()->routeIs('admin.admin-access.*') ? 'active' : '' }}">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Akses Admin</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif

                        {{-- Integrasi API --}}
                        @if($menuUser->hasAdminPermission('integrations'))
                            <li class="nav-item has-treeview {{ request()->is('admin/integration-tokens*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ request()->is('admin/integration-tokens*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-key"></i>
                                    <p>Integrasi API<i class="right fas fa-angle-left"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.integration-tokens.index') }}"
                                            class="nav-link {{ request()->routeIs('admin.integration-tokens.index') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Token API</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.integration-tokens.docs') }}"
                                            class="nav-link {{ request()->routeIs('admin.integration-tokens.docs') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Dokumentasi API</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        {{-- Karyawan --}}
                        @if($menuUser->hasAdminPermission('karyawan'))
                            <li class="nav-item">
                                <a href="{{ route('admin.karyawan.index') }}"
                                    class="nav-link {{ request()->routeIs('admin.karyawan*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Karyawan</p>
                                </a>
                            </li>
                        @endif

                        {{-- Lowongan & Pelamar --}}
                        @if($menuUser->hasAdminPermission('jobs'))
                            <li class="nav-item has-treeview {{ request()->is('admin/jobs*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ request()->is('admin/jobs*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-briefcase"></i>
                                    <p>Lowongan & Pelamar<i class="right fas fa-angle-left"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.jobs.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Data Lowongan</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.jobs.applicants.all') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Pelamar Pekerjaan</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        {{-- Job Todo --}}
                        @if($menuUser->hasAdminPermission('job_todos'))
                            <li class="nav-item">
                                <a href="{{ route('admin.job-todos.index') }}"
                                    class="nav-link {{ request()->routeIs('admin.job-todos*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <p>Job Todo</p>
                                </a>
                            </li>
                        @endif

                        {{-- Absensi --}}
                        @if($menuUser->hasAdminPermission('absensi'))
                            <li class="nav-item">
                                <a href="{{ route('admin.absensi') }}" class="nav-link {{ request()->routeIs('admin.absensi*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-check"></i>
                                    <p>Absensi</p>
                                </a>
                            </li>
                        @endif

                        {{-- Lembur --}}
                        @if($menuUser->hasAdminPermission('lembur'))
                            <li class="nav-item">
                                <a href="{{ route('admin.lembur') }}" class="nav-link {{ request()->routeIs('admin.lembur*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-clock"></i>
                                    <p>Lembur</p>
                                </a>
                            </li>
                        @endif

                        {{-- Jadwal --}}
                        @if($menuUser->hasAdminPermission('jadwal'))
                            <li class="nav-item">
                                <a href="{{ route('admin.jadwal') }}" class="nav-link {{ request()->routeIs('admin.jadwal*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-calendar-alt"></i>
                                    <p>Jadwal Kerja</p>
                                </a>
                            </li>
                        @endif

                        {{-- Gaji --}}
                        @if($menuUser->hasAdminPermission('gaji'))
                            <li class="nav-item">
                                <a href="{{ route('admin.gaji') }}" class="nav-link {{ request()->routeIs('admin.gaji*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-money-bill-wave"></i>
                                    <p>Gaji</p>
                                </a>
                            </li>
                        @endif

                        {{-- Laporan --}}
                        @if($menuUser->hasAdminPermission('laporan'))
                            <li class="nav-item">
                                <a href="{{ route('admin.laporan') }}" class="nav-link {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <p>Laporan</p>
                                </a>
                            </li>
                        @endif

                        {{-- Pelanggaran --}}
                        @if($menuUser->hasAdminPermission('pelanggaran'))
                            <li class="nav-item has-treeview {{ request()->is('admin/pelanggaran*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ request()->is('admin/pelanggaran*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-exclamation-triangle"></i>
                                    <p>Pelanggaran<i class="right fas fa-angle-left"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.pelanggaran.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Log Pelanggaran</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.pelanggaran.create') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Tambah Pelanggaran</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        {{-- 🔥 PENGUMUMAN (ANNOUNCEMENTS) --}}
                        @if($menuUser->hasAdminPermission('announcements'))
                            <li class="nav-item">
                                <a href="{{ route('admin.announcements.index') }}"
                                    class="nav-link {{ request()->is('admin/announcements*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-bullhorn"></i>
                                    <p>Pengumuman</p>
                                </a>
                            </li>
                        @endif


                        {{-- Master Data --}}
                        @if($menuUser->hasAdminPermission('pelanggaran'))
                            <li
                                class="nav-item has-treeview {{ request()->is('admin/pelanggaran/master*') ? 'menu-open' : '' }}">
                                <a href="#"
                                    class="nav-link {{ request()->is('admin/pelanggaran/master*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-database"></i>
                                    <p>Master Data<i class="right fas fa-angle-left"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.pelanggaran.master.jabatan.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master Jabatan</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.pelanggaran.master.lokasi.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master Lokasi</p>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.pelanggaran.master.kode.index') }}" class="nav-link">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Master Kode Pelanggaran</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        {{-- Aturan Potongan Gaji --}}
                        @if($menuUser->hasAdminPermission('potongan'))
                            <li class="nav-item">
                                <a href="{{ route('admin.salary-deduction-rules.index') }}"
                                    class="nav-link {{ request()->routeIs('admin.salary-deduction-rules*') || request()->routeIs('admin.potongan-gaji*') ? 'active' : '' }}">
                                    <i class="fas fa-hand-holding-usd nav-icon"></i>
                                    <p>Aturan Potongan Gaji</p>
                                </a>
                            </li>
                        @endif

                        {{-- 🔥 SUBMISSION (BARU) --}}
                        @if($menuUser->hasAdminPermission('submission') || $menuUser->hasAdminPermission('submission_types'))
                            <li class="nav-item has-treeview {{ request()->is('admin/submission*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ request()->is('admin/submission*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-signature"></i>
                                    <p>Submission<i class="right fas fa-angle-left"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @if($menuUser->hasAdminPermission('submission'))
                                        <li class="nav-item">
                                            <a href="{{ route('admin.submission.index') }}" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Pengajuan Masuk</p>
                                            </a>
                                        </li>
                                    @endif
                                    @if($menuUser->hasAdminPermission('submission_types'))
                                        <li class="nav-item">
                                            <a href="{{ route('admin.submission-types.index') }}" class="nav-link">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Jenis Pengajuan</p>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif


                        {{-- Logout --}}
                        <li class="nav-item mt-3">
                            <a href="{{ route('logout') }}" class="nav-link text-danger"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
        </aside>

        {{-- CONTENT --}}
        <div class="content-wrapper">
            <section class="content p-4">
                @yield('content')
            </section>
        </div>

    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        var root = document.documentElement;
        var btn = document.getElementById('themeToggleBtn');
        var icon = document.getElementById('themeToggleIcon');
        var storageKey = 'theme_mode';

        function updateTheme(theme) {
            root.setAttribute('data-theme', theme);
            try {
                localStorage.setItem(storageKey, theme);
            } catch (e) {}

            if (!icon || !btn) return;

            var isDark = theme === 'dark';
            icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
            btn.title = isDark ? 'Aktifkan mode siang' : 'Aktifkan mode malam';
            btn.setAttribute(
                'aria-label',
                isDark ? 'Aktifkan mode siang' : 'Aktifkan mode malam'
            );
        }

        if (btn) {
            updateTheme(root.getAttribute('data-theme') || 'light');
            btn.addEventListener('click', function () {
                var current = root.getAttribute('data-theme') || 'light';
                updateTheme(current === 'dark' ? 'light' : 'dark');
            });
        }
    })();
</script>

@stack('scripts')

</body>

</html>
