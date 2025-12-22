<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Admin Panel'))</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

    {{-- NAVBAR --}}
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

    {{-- SIDEBAR --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('admin.dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">
                {{ config('app.name', 'Admin Panel') }}
            </span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column">

                    {{-- Dashboard --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}"
                           class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    {{-- Karyawan --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.karyawan.index') }}"
                           class="nav-link {{ request()->routeIs('admin.karyawan*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Karyawan</p>
                        </a>
                    </li>

                    {{-- Absensi --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.absensi') }}"
                           class="nav-link {{ request()->routeIs('admin.absensi*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-check"></i>
                            <p>Absensi</p>
                        </a>
                    </li>

                    {{-- Lembur --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.lembur') }}"
                           class="nav-link {{ request()->routeIs('admin.lembur*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-clock"></i>
                            <p>Lembur</p>
                        </a>
                    </li>

                    {{-- Jadwal --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.jadwal') }}"
                           class="nav-link {{ request()->routeIs('admin.jadwal*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calendar-alt"></i>
                            <p>Jadwal Kerja</p>
                        </a>
                    </li>

                    {{-- Gaji --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.gaji') }}"
                           class="nav-link {{ request()->routeIs('admin.gaji*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-money-bill-wave"></i>
                            <p>Gaji</p>
                        </a>
                    </li>

                    {{-- Laporan --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.laporan') }}"
                           class="nav-link {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Laporan</p>
                        </a>
                    </li>

                    {{-- Portal Karyawan --}}
                    <li class="nav-item">
                        <a href="http://localhost:3000" target="_blank" class="nav-link">
                            <i class="nav-icon fas fa-external-link-alt"></i>
                            <p>Portal Karyawan</p>
                        </a>
                    </li>

                    {{-- Logout --}}
                    <li class="nav-item mt-3">
                        <a href="{{ route('logout') }}"
                           class="nav-link text-danger"
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
    <div class="content-wrapper p-4">
        @yield('content')
    </div>

</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
    @csrf
</form>

<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

@stack('scripts')
</body>
</html>
