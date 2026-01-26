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

<body class="hold-transition sidebar-mini layout-fixed">

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
            <a href="{{ route('admin.dashboard') }}" class="brand-link text-center">
                <img src="{{ asset('images/logo-sm.svg') }}" alt="Logo SM" style="max-height:50px;">
                <div class="brand-text font-weight-light mt-2">
                    Sidomulyo Advertising
                </div>
                <div style="font-size:12px;opacity:.7;">
                    v{{ config('app.app_version', '1.0.0') }}
                </div>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">

                        {{-- Dashboard --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}"
                                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-home"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        {{-- Manajemen User --}}
                        @if(Auth::user()->isAdmin())
                            <li class="nav-item has-treeview {{ request()->is('admin/users*') ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-cog"></i>
                                    <p>Manajemen User<i class="right fas fa-angle-left"></i></p>
                                </a>
                                <ul class="nav nav-treeview">
                                    <li class="nav-item">
                                        <a href="{{ route('admin.users.index') }}"
                                            class="nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>Daftar User</p>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        {{-- Karyawan --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.karyawan.index') }}"
                                class="nav-link {{ request()->routeIs('admin.karyawan*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Karyawan</p>
                            </a>
                        </li>

                        {{-- Lowongan & Pelamar --}}
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

                        {{-- Job Todo --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.job-todos.index') }}"
                                class="nav-link {{ request()->routeIs('admin.job-todos*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tasks"></i>
                                <p>Job Todo</p>
                            </a>
                        </li>

                        {{-- Absensi --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.absensi') }}" class="nav-link">
                                <i class="nav-icon fas fa-user-check"></i>
                                <p>Absensi</p>
                            </a>
                        </li>

                        {{-- Lembur --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.lembur') }}" class="nav-link">
                                <i class="nav-icon fas fa-clock"></i>
                                <p>Lembur</p>
                            </a>
                        </li>

                        {{-- Jadwal --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.jadwal') }}" class="nav-link">
                                <i class="nav-icon fas fa-calendar-alt"></i>
                                <p>Jadwal Kerja</p>
                            </a>
                        </li>

                        {{-- Gaji --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.gaji') }}" class="nav-link">
                                <i class="nav-icon fas fa-money-bill-wave"></i>
                                <p>Gaji</p>
                            </a>
                        </li>

                        {{-- Laporan --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.laporan') }}" class="nav-link">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Laporan</p>
                            </a>
                        </li>

                        {{-- Pelanggaran --}}
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
                        {{-- 🔥 PENGUMUMAN (ANNOUNCEMENTS) --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.announcements.index') }}"
                                class="nav-link {{ request()->is('admin/announcements*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-bullhorn"></i>
                                <p>Pengumuman</p>
                            </a>
                        </li>


                        {{-- Master Data --}}
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

                        {{-- Aturan Potongan Gaji --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.salary-deduction-rules.index') }}"
                                class="nav-link {{ request()->routeIs('admin.salary-deduction-rules*') ? 'active' : '' }}">
                                <i class="fas fa-hand-holding-usd nav-icon"></i>
                                <p>Aturan Potongan Gaji</p>
                            </a>
                        </li>

                        {{-- 🔥 SUBMISSION (BARU) --}}
                        <li class="nav-item has-treeview {{ request()->is('admin/submission*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->is('admin/submission*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-signature"></i>
                                <p>Submission<i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.submission.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Pengajuan Masuk</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.submission-types.index') }}" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Jenis Pengajuan</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Portal --}}
                        <li class="nav-item">
                            <a href="http://localhost:3000" target="_blank" class="nav-link">
                                <i class="nav-icon fas fa-external-link-alt"></i>
                                <p>Portal Karyawan</p>
                            </a>
                        </li>

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

    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    @stack('scripts')
</body>

</html>