<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Panel Admin'))</title>

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
    <link href="https://fonts.bunny.net/css?family=Inter:400,500,600,700,800" rel="stylesheet">

    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --accent: #3f6fea;
            --accent-soft: #edf4ff;
            --accent-strong: #244fc2;
            --ok: #2b9a67;
            --warning: #d99522;
            --danger: #d45d45;
        }

        :root[data-theme='light'] {
            --bg: #f2f5f9;
            --surface: #ffffff;
            --surface-2: #f7f9fc;
            --text: #20293a;
            --text-muted: #64748b;
            --border: #e4e9f1;
            --sidebar-bg: #141821;
            --sidebar-bg-2: #11151d;
            --sidebar-text: #aeb7c6;
            --sidebar-hover: rgba(255, 255, 255, .08);
            --sidebar-active-bg: rgba(255, 255, 255, .12);
            --sidebar-active-text: #ffffff;
            --navbar-bg: #ffffff;
            --shadow: 0 14px 34px rgba(22, 34, 51, .08);
        }

        :root[data-theme='dark'] {
            --bg: #101721;
            --surface: #1a2433;
            --surface-2: #202c3e;
            --text: #e2e9f5;
            --text-muted: #9eb0cc;
            --border: #2f3c51;
            --sidebar-bg: #0d1622;
            --sidebar-bg-2: #101a28;
            --sidebar-text: #a5b6d2;
            --sidebar-hover: rgba(255, 255, 255, .07);
            --sidebar-active-bg: rgba(255, 255, 255, .11);
            --sidebar-active-text: #ffffff;
            --navbar-bg: #1a2433;
            --shadow: 0 12px 28px rgba(0, 0, 0, .35);
        }

        html,
        body {
            background: var(--bg) !important;
            color: var(--text);
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 14px;
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

        .content-wrapper .content {
            padding: 22px !important;
        }

        .main-header.navbar {
            background: var(--navbar-bg) !important;
            border-bottom: 1px solid var(--border);
            box-shadow: 0 8px 24px rgba(22, 34, 51, .05);
            min-height: 62px;
            padding-left: 1rem;
            padding-right: 1rem;
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
            background-image: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--sidebar-bg-2) 72%, #17245d 140%) !important;
        }

        .main-sidebar .brand-link {
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            background: transparent !important;
            min-height: 72px;
            padding: .75rem .75rem;
            margin-bottom: .55rem;
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

        .sidebar-brand-stack .app-logo-text {
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: .3em;
            padding-left: .3em;
            color: #fff;
            line-height: 1;
        }

        .sidebar-brand-stack .app-version {
            font-size: .68rem;
            font-weight: 700;
            color: rgba(255, 255, 255, .72);
            letter-spacing: .25px;
        }

        body.sidebar-collapse .sidebar-brand-stack {
            padding-left: .35rem;
            padding-right: .35rem;
        }

        body.sidebar-collapse .sidebar-brand-stack .app-logo-text {
            font-size: 1.05rem;
            letter-spacing: .12em;
            padding-left: .12em;
        }

        body.sidebar-collapse .sidebar-brand-stack .app-version {
            display: none;
        }

        body.sidebar-collapse .main-sidebar .nav-link {
            justify-content: center;
            margin-left: 10px;
            margin-right: 10px;
            padding-left: 0;
            padding-right: 0;
        }

        body.sidebar-collapse .main-sidebar .nav-link .nav-icon {
            margin-right: 0;
        }

        /* Sembunyikan teks label saat sidebar di-collapse agar tidak meluber/terpotong */
        body.sidebar-collapse .main-sidebar .nav-link > p:not(.right),
        body.sidebar-collapse .main-sidebar .nav-link .right {
            display: none;
        }

        /* Ikon tetap rata tengah saat collapse */
        body.sidebar-collapse .main-sidebar .nav-link {
            padding-left: 0;
            padding-right: 0;
            justify-content: center;
        }

        body.sidebar-collapse .main-sidebar .nav-link .nav-icon {
            margin-right: 0;
        }

        /* ⛔ Nonaktifkan hover-otomatis submenu saat sidebar collapse */
        body.sidebar-collapse.sidebar-mini .main-sidebar .nav-sidebar .nav-item.has-treeview:hover .nav-treeview,
        body.sidebar-collapse .main-sidebar .nav-sidebar .nav-item.has-treeview:hover > .nav-treeview {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
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
            margin: 4px 10px;
            min-height: 42px;
            display: flex;
            align-items: center;
            font-weight: 600;
        }

        .main-sidebar .nav-link .nav-icon {
            font-size: .98rem;
            margin-right: .62rem;
            opacity: .86;
            text-align: center;
            width: 1.35rem;
        }

        .main-sidebar .nav-treeview .nav-link {
            font-size: .88rem;
            margin-left: 18px;
            min-height: 36px;
            padding-left: .8rem;
        }

        .main-sidebar .nav-sidebar > .nav-item:first-child {
            margin-top: .35rem;
        }

        .main-sidebar .nav-header {
            color: rgba(255, 255, 255, .42) !important;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .13em;
            text-transform: uppercase;
            padding: 1.05rem 1.2rem .3rem;
            margin: .35rem .1rem 0;
            border-top: 1px solid rgba(255, 255, 255, .07);
        }

        .main-sidebar .nav-sidebar > .nav-header:first-child {
            border-top: 0;
            margin-top: 0;
            padding-top: .25rem;
        }

        body.sidebar-collapse .main-sidebar .nav-header {
            display: none;
        }

        .main-sidebar .nav-divider-top {
            border-top: 1px solid rgba(255, 255, 255, .1);
            margin-top: .4rem;
            padding-top: .15rem;
        }

        .main-sidebar .nav-link:hover {
            background: var(--sidebar-hover) !important;
            color: #fff !important;
        }

        .main-sidebar .nav-link.active {
            background: var(--sidebar-active-bg) !important;
            color: var(--sidebar-active-text) !important;
            box-shadow: inset 3px 0 0 #6f8eff;
        }

        .main-sidebar .nav-link.active .nav-icon,
        .main-sidebar .nav-link:hover .nav-icon {
            opacity: 1;
        }

        .card,
        .small-box,
        .info-box,
        .modal-content {
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            border-radius: 14px;
        }

        .card-header,
        .card-footer {
            background: var(--surface-2);
            border-color: var(--border);
            padding: 1rem 1.15rem;
        }

        .card-title {
            font-weight: 800;
            margin-bottom: 0;
        }

        .table,
        .table td,
        .table th,
        .table thead th {
            color: var(--text);
            border-color: var(--border);
        }

        .table {
            margin-bottom: 0;
        }

        .table td,
        .table th {
            padding: .86rem .95rem;
            vertical-align: middle;
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
            border-radius: 10px;
            min-height: 40px;
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
            min-height: 36px;
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

        .bg-primary,
        .bg-info,
        .bg-success,
        .bg-warning,
        .bg-danger,
        .bg-secondary,
        .bg-dark {
            color: #fff !important;
        }

        .bg-primary { background-color: #3f6fea !important; }
        .bg-info { background-color: #249393 !important; }
        .bg-success { background-color: #2b9a67 !important; }
        .bg-warning { background-color: #e0a025 !important; }
        .bg-danger { background-color: #d45d45 !important; }
        .bg-secondary { background-color: #718097 !important; }
        .bg-dark { background-color: #263449 !important; }

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

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > .d-flex:first-child,
        .content-wrapper .content > .d-flex:first-child,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > h1:first-child,
        .content-wrapper .content > h1:first-child,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > h2:first-child,
        .content-wrapper .content > h2:first-child,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > h4:first-child,
        .content-wrapper .content > h4:first-child {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow);
            margin-bottom: 22px !important;
            padding: 20px 22px;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > .d-flex:first-child h1,
        .content-wrapper .content > .d-flex:first-child h1,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > .d-flex:first-child h4,
        .content-wrapper .content > .d-flex:first-child h4,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > h1:first-child,
        .content-wrapper .content > h1:first-child,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > h2:first-child,
        .content-wrapper .content > h2:first-child,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > h4:first-child,
        .content-wrapper .content > h4:first-child {
            color: var(--text);
            font-size: 1.45rem;
            font-weight: 800;
            line-height: 1.25;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > .d-flex:first-child .btn-group,
        .content-wrapper .content > .d-flex:first-child .btn-group,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > .d-flex:first-child > a,
        .content-wrapper .content > .d-flex:first-child > a,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > .d-flex:first-child > div:last-child,
        .content-wrapper .content > .d-flex:first-child > div:last-child {
            flex-wrap: wrap;
            gap: 8px;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > .card,
        .content-wrapper .content > .card,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) > form > .card,
        .content-wrapper .content > form > .card,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .card.shadow-sm,
        .content-wrapper .content > .row .card {
            border-radius: 16px;
            overflow: hidden;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .card-header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .card-header h3,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .card-header h5,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .card-header h6,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .card-title {
            color: var(--text);
            font-size: 1.02rem;
            font-weight: 800;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .table {
            font-size: .92rem;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .table thead th {
            background: var(--surface-2) !important;
            border-bottom: 1px solid var(--border);
            color: var(--text-muted);
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: 0;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .table tbody td {
            border-color: var(--border);
            color: var(--text);
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .form-group label,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) label {
            color: var(--text);
            font-weight: 700;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box {
            background: var(--surface) !important;
            border: 1px solid var(--border);
            border-left: 4px solid var(--accent);
            border-radius: 14px;
            box-shadow: var(--shadow);
            color: var(--text) !important;
            min-height: 128px;
            overflow: hidden;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box .inner {
            padding: 18px;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box h3 {
            color: var(--text) !important;
            font-size: 1.85rem;
            font-weight: 800;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box p {
            color: var(--text-muted) !important;
            font-weight: 700;
            margin-bottom: 0;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box .icon {
            color: var(--text-muted);
            opacity: .16;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box .small-box-footer {
            background: var(--surface-2);
            border-top: 1px solid var(--border);
            color: var(--accent-strong) !important;
            font-weight: 700;
        }

        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box.bg-info,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box.bg-primary { border-left-color: #3f6fea; }
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box.bg-success { border-left-color: #2b9a67; }
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box.bg-warning { border-left-color: #e0a025; }
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box.bg-danger { border-left-color: #d45d45; }
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box.bg-secondary,
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box.bg-dark { border-left-color: #718097; }
        .content-wrapper .container-fluid:not(.dashboard-pro):not(.user-management-modern) .small-box.bg-teal { border-left-color: #249393; }

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
                                        'title' => 'Pengajuan Baru',
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
                                title="Percakapan">
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
                                <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                            </a>
                        </div>
                    </li>
                @endauth
            </ul>
        </nav>

        {{-- ================= SIDEBAR ================= --}}
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('admin.dashboard') }}" class="brand-link text-center sidebar-brand-stack">
                <span class="app-logo-text">SIHR</span>
                <span class="app-version">v{{ config('app.app_version', '1.0.0') }}</span>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        @php
                            $menuUser = Auth::user();

                            $showKepegawaian = $menuUser->hasAdminPermission('karyawan')
                                || $menuUser->hasAdminPermission('jobs')
                                || $menuUser->hasAdminPermission('job_todos');

                            $showKehadiran = $menuUser->hasAdminPermission('absensi')
                                || $menuUser->hasAdminPermission('lembur')
                                || $menuUser->hasAdminPermission('jadwal')
                                || $menuUser->hasAdminPermission('submission')
                                || $menuUser->hasAdminPermission('submission_types')
                                || $menuUser->hasAdminPermission('announcements');

                            $showKeuangan = $menuUser->hasAdminPermission('gaji')
                                || $menuUser->hasAdminPermission('potongan')
                                || $menuUser->hasAdminPermission('laporan');

                            $showKedisiplinan = $menuUser->hasAdminPermission('pelanggaran');

                            $showPengaturan = $menuUser->hasAdminPermission('users')
                                || $menuUser->hasAdminPermission('manage_admin_access')
                                || $menuUser->hasAdminPermission('integrations');
                        @endphp

                        {{-- Dasbor --}}
                        @if($menuUser->hasAdminPermission('dashboard'))
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}"
                                    class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-home"></i>
                                    <p>Dasbor</p>
                                </a>
                            </li>
                        @endif

                        {{-- ============================ KEPEGAWAIAN ============================ --}}
                        @if($showKepegawaian)
                            <li class="nav-header">Kepegawaian</li>

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

                            {{-- Tugas Kerja --}}
                            @if($menuUser->hasAdminPermission('job_todos'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.job-todos.index') }}"
                                        class="nav-link {{ request()->routeIs('admin.job-todos*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-tasks"></i>
                                        <p>Tugas Kerja</p>
                                    </a>
                                </li>
                            @endif

                            {{-- Lowongan & Pelamar --}}
                            @if($menuUser->hasAdminPermission('jobs'))
                                <li class="nav-item has-treeview {{ request()->is('admin/jobs*') ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ request()->is('admin/jobs*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-briefcase"></i>
                                        <p>Lowongan &amp; Pelamar<i class="right fas fa-angle-left"></i></p>
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
                        @endif

                        {{-- ============================ KEHADIRAN ============================ --}}
                        @if($showKehadiran)
                            <li class="nav-header">Kehadiran</li>

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

                            {{-- Jadwal Kerja --}}
                            @if($menuUser->hasAdminPermission('jadwal'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.jadwal') }}" class="nav-link {{ request()->routeIs('admin.jadwal*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-calendar-alt"></i>
                                        <p>Jadwal Kerja</p>
                                    </a>
                                </li>
                            @endif

                            {{-- Lokasi Absensi (Geofence) --}}
                            @if($menuUser->hasAdminPermission('absensi'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.lokasi-absensi.index') }}" class="nav-link {{ request()->routeIs('admin.lokasi-absensi.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-map-marker-alt"></i>
                                        <p>Lokasi Absensi</p>
                                    </a>
                                </li>
                            @endif

                            {{-- Pengajuan --}}
                            @if($menuUser->hasAdminPermission('submission') || $menuUser->hasAdminPermission('submission_types'))
                                <li class="nav-item has-treeview {{ request()->is('admin/submission*') ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ request()->is('admin/submission*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-file-signature"></i>
                                        <p>Pengajuan<i class="right fas fa-angle-left"></i></p>
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

                            {{-- Pengumuman --}}
                            @if($menuUser->hasAdminPermission('announcements'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.announcements.index') }}"
                                        class="nav-link {{ request()->is('admin/announcements*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-bullhorn"></i>
                                        <p>Pengumuman</p>
                                    </a>
                                </li>
                            @endif
                        @endif

                        {{-- ============================ KEUANGAN ============================ --}}
                        @if($showKeuangan)
                            <li class="nav-header">Keuangan</li>

                            {{-- Gaji --}}
                            @if($menuUser->hasAdminPermission('gaji'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.gaji') }}" class="nav-link {{ request()->routeIs('admin.gaji*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-money-bill-wave"></i>
                                        <p>Gaji</p>
                                    </a>
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

                            {{-- Laporan --}}
                            @if($menuUser->hasAdminPermission('laporan'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.laporan') }}" class="nav-link {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-file-alt"></i>
                                        <p>Laporan</p>
                                    </a>
                                </li>
                            @endif
                        @endif

                        {{-- ============================ KEDISIPLINAN ============================ --}}
                        @if($showKedisiplinan)
                            <li class="nav-header">Kedisiplinan</li>

                            {{-- Log Pelanggaran --}}
                            <li class="nav-item">
                                <a href="{{ route('admin.pelanggaran.index') }}"
                                    class="nav-link {{ request()->routeIs('admin.pelanggaran.index') || request()->routeIs('admin.pelanggaran.riwayat') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-exclamation-triangle"></i>
                                    <p>Log Pelanggaran</p>
                                </a>
                            </li>

                            {{-- Laporan Rekan --}}
                            <li class="nav-item">
                                <a href="{{ route('admin.peer-reports.index') }}"
                                    class="nav-link {{ request()->routeIs('admin.peer-reports.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-friends"></i>
                                    <p>
                                        Laporan Rekan
                                        @php $pendingPeer = \App\Models\PeerReport::where('status','pending')->count(); @endphp
                                        @if($pendingPeer > 0)
                                            <span class="badge badge-warning right">{{ $pendingPeer }}</span>
                                        @endif
                                    </p>
                                </a>
                            </li>

                            {{-- Input Pelanggaran --}}
                            <li class="nav-item">
                                <a href="{{ route('admin.pelanggaran.create') }}"
                                    class="nav-link {{ request()->routeIs('admin.pelanggaran.create') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-plus-circle"></i>
                                    <p>Input Pelanggaran</p>
                                </a>
                            </li>

                            {{-- Master Data --}}
                            <li class="nav-item has-treeview {{ request()->is('admin/pelanggaran/master*') ? 'menu-open' : '' }}">
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

                        {{-- ============================ PENGATURAN ============================ --}}
                        @if($showPengaturan)
                            <li class="nav-header">Pengaturan</li>

                            {{-- Manajemen Pengguna --}}
                            @if($menuUser->hasAdminPermission('users') || $menuUser->hasAdminPermission('manage_admin_access'))
                                <li class="nav-item has-treeview {{ request()->is('admin/users*') || request()->is('admin/admin-access*') ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ request()->is('admin/users*') || request()->is('admin/admin-access*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user-cog"></i>
                                        <p>Manajemen Pengguna<i class="right fas fa-angle-left"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @if($menuUser->hasAdminPermission('users'))
                                            <li class="nav-item">
                                                <a href="{{ route('admin.users.index') }}"
                                                    class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                                    <i class="far fa-circle nav-icon"></i>
                                                    <p>Daftar Pengguna</p>
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
                                <li class="nav-item has-treeview {{ request()->is('admin/integration-tokens*') || request()->is('admin/integration-webhooks*') ? 'menu-open' : '' }}">
                                    <a href="#" class="nav-link {{ request()->is('admin/integration-tokens*') || request()->is('admin/integration-webhooks*') ? 'active' : '' }}">
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
                                        <li class="nav-item">
                                            <a href="{{ route('admin.integration-webhooks.index') }}"
                                                class="nav-link {{ request()->routeIs('admin.integration-webhooks.*') ? 'active' : '' }}">
                                                <i class="far fa-circle nav-icon"></i>
                                                <p>Webhook (Notifikasi)</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif
                        @endif

                        {{-- Keluar --}}
                        <li class="nav-item nav-divider-top mt-2">
                            <a href="{{ route('logout') }}" class="nav-link text-danger"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Keluar</p>
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
